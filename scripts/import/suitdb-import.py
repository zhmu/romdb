#!/usr/bin/env python3

import os, sys
sys.path.append(os.path.join(os.path.dirname(__file__), '..', 'lib'))
import romdb
from config import Configuration
from dbconnection import DBConnection

dbxmlfname = Configuration.xml_dir() + "/db_suitobject.xml"
filedbfname = Configuration.data_dir() + "/suitobject.db"

conn = DBConnection()
cur = conn.cursor()

fields = romdb.load_field_xml(dbxmlfname)

db_fields = romdb.DBFields(cur, 'suit', fields, [ 'basetype', 'suitlist' ])
db_fields.recreate_table([ ('guid', 'sys_name', 'id') ])

cur.execute('DROP TABLE IF EXISTS suit_bonus')
cur.execute("""CREATE TABLE suit_bonus (
	guid INTEGER NOT NULL,
	itemcount INTEGER NOT NULL,
	ordernum INTEGER NOT NULL,
	typeid INTEGER NOT NULL,
	value INTEGER NOT NULL,
	CONSTRAINT suitbouns_itemcount_range CHECK (itemcount >= 2 AND itemcount <= 9),
	CONSTRAINT suitbonus_ordernum_range CHECK (ordernum >= 1 AND ordernum <= 3),
	PRIMARY KEY (guid, itemcount, ordernum),
	FOREIGN KEY (typeid) REFERENCES sys_weareqtype (id),
	FOREIGN KEY (guid) REFERENCES suit 
)""")

cur.execute('DROP TABLE IF EXISTS suit_itemlist ')
cur.execute("""CREATE TABLE suit_itemlist (
	suitid INTEGER NOT NULL,
	ordernum INTEGER NOT NULL,
	weaponid INTEGER,
	armorid INTEGER,
	CONSTRAINT suititemlist_ordernum_range CHECK (ordernum >= 1 AND ordernum <= 10),
	CONSTRAINT suititemlist_weapon_or_armor CHECK ((weaponid IS NULL AND armorid IS NOT NULL) OR (weaponid IS NOT NULL AND armorid IS NULL)),
	FOREIGN KEY (suitid) REFERENCES suit (guid),
	FOREIGN KEY (weaponid) REFERENCES weapon (guid),
	FOREIGN KEY (armorid) REFERENCES armor (guid)
)""")
conn.commit()

records = romdb.decode_db(filedbfname, fields)
for k, v in records.items():
	if not db_fields.insert_fields(v):
		# constraint failure (no sys_name most likely) - skip the item
		continue

	for num in range(1, 10):
		for i in range(1, 4):
			typeid = v['basetype' + str(num) + '_' + str(i)]
			typeval = v['basetypevalue' + str(num) + '_' + str(i)]
			if typeid != 0 and typeid != 214 and typeid != 216 and typeid != 215: # XXXRS itemset 610312 has this
				cur.execute('INSERT INTO suit_bonus (guid, itemcount, ordernum, typeid, value) VALUES (%s,%s,%s,%s,%s)', (k, num + 1, i, typeid, typeval))

	try:
		for i in range(1, 11):
			suitlist = v['suitlist' + str(i)]
			if suitlist != 0:
				# check for weapon or armor
				cur.execute("SELECT NULL FROM weapon WHERE guid=%s", (suitlist, ))
				if cur.fetchone():
					cur.execute('INSERT INTO suit_itemlist (suitid, ordernum, weaponid) VALUES (%s,%s,%s)', (k, i, suitlist))
				else:
					cur.execute('INSERT INTO suit_itemlist (suitid, ordernum, armorid) VALUES (%s,%s,%s)', (k, i, suitlist))
	except DBConnection.IntegrityError:
		# constraint failure (no armor/weapon) - skip the item
		conn.rollback()
		continue

	conn.commit()

conn.close()

# vim:set ts=2 sw=2:
