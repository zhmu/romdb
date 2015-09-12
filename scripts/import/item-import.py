#!/usr/bin/env python3

import os, sys
sys.path.append(os.path.join(os.path.dirname(__file__), '..', 'lib'))
import romdb
from config import Configuration
from dbconnection import DBConnection

dbxmlfname = Configuration.xml_dir() + "/db_itemobject.xml"
filedbfname = Configuration.data_dir() + "/itemobject.db"

conn = DBConnection()
cur = conn.cursor()

fields = romdb.load_field_xml(dbxmlfname)

db_fields = romdb.DBFields(cur, 'item', fields, [ 'dropability' ])
db_fields.recreate_table([ ])

cur.execute('DROP TABLE IF EXISTS item_ability')
cur.execute("""CREATE TABLE item_ability (
	guid INTEGER NOT NULL,
	ordernum INTEGER NOT NULL,
	abilityid INTEGER NOT NULL,
	CONSTRAINT item_ability_ordernum_range CHECK (ordernum >= 1 AND ordernum <= 6),
	CONSTRAINT item_ability_ability_range CHECK (abilityid / 10000 IN (51, 72)),
	PRIMARY KEY (guid, ordernum),
	FOREIGN KEY (guid) REFERENCES item
)
""")

records = romdb.decode_db(filedbfname, fields)
for k, v in records.items():
	if not db_fields.insert_fields(v):
		# constraint failure (no sys_name most likely) - skip the item
		continue

	for i in range(1, 7):
		abl = v['dropability' + str(i)]
		if abl != 0:
			cur.execute('INSERT INTO item_ability (guid, ordernum, abilityid) VALUES (%s,%s,%s)', (k, i, abl))
	conn.commit()

conn.close()

# vim:set ts=2 sw=2:
