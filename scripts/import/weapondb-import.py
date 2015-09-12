#!/usr/bin/env python3

import os, sys
sys.path.append(os.path.join(os.path.dirname(__file__), '..', 'lib'))
import romdb
from config import Configuration
from dbconnection import DBConnection

dbxmlfname = Configuration.xml_dir() + "/db_weaponobject.xml"
filedbfname = Configuration.data_dir() + "/weaponobject.db"

conn = DBConnection()
cur = conn.cursor()

fields = romdb.load_field_xml(dbxmlfname)

# construct db table
db_fields = romdb.DBFields(cur, 'weapon', fields, [ 'eqtype', 'dropability' ])
db_fields.recreate_table([ ('guid', 'sys_name', 'id') ])

cur.execute('DROP TABLE IF EXISTS weapon_ability')
cur.execute("""CREATE TABLE weapon_ability (
	guid INTEGER NOT NULL,
	ordernum INTEGER NOT NULL,
	abilityid INTEGER NOT NULL,
	CONSTRAINT weapon_ability_ordernum_range CHECK (ordernum >= 1 AND ordernum <= 6),
	CONSTRAINT weapon_ability_ability_range CHECK (abilityid / 10000 IN (51, 72)),
	PRIMARY KEY (guid, ordernum),
	FOREIGN KEY (guid) REFERENCES weapon
)
""")

cur.execute('DROP TABLE IF EXISTS weapon_weareq ')
cur.execute("""CREATE TABLE weapon_weareq (
	guid INTEGER NOT NULL,
	ordernum INTEGER NOT NULL,
	typeid INTEGER NOT NULL,
	value INTEGER NOT NULL,
	CONSTRAINT weapon_weareq_ordernum_range CHECK (ordernum >= 1 AND ordernum <= 10),
	PRIMARY KEY (guid, ordernum),
	FOREIGN KEY (guid) REFERENCES weapon,
	FOREIGN KEY (typeid) REFERENCES sys_weareqtype (id)
)
""")

conn.commit()

records = romdb.decode_db(filedbfname, fields)
for k, v in records.items():
	if not db_fields.insert_fields(v):
		# constraint failure (no sys_name most likely) - skip the item
		continue

	for i in range(1, 7):
		abl = v['dropability' + str(i)]
		if abl != 0:
			cur.execute('INSERT INTO weapon_ability (guid, ordernum, abilityid) VALUES (%s,%s,%s)', (k, i, abl))

	for i in range(1, 11):
		typeid = v['eqtype' + str(i)]
		typeval = v['eqtypevalue' + str(i)]
		if typeid != 0:
			cur.execute('INSERT INTO weapon_weareq (guid, ordernum, typeid, value) VALUES (%s,%s,%s,%s)', (k, i, typeid, typeval))
	conn.commit()

# finally, throw away weapon types where we have not a single item for
cur.execute('DELETE FROM sys_weapontype w WHERE NOT EXISTS (SELECT NULL FROM weapon WHERE weapontype=w.id)')
conn.commit()

conn.close()

# vim:set ts=2 sw=2:
