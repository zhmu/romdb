#!/usr/bin/env python3

import os, sys
sys.path.append(os.path.join(os.path.dirname(__file__), '..', 'lib'))
import romdb
from config import Configuration
from dbconnection import DBConnection

dbxmlfname = Configuration.xml_dir() + "/db_treasureobject.xml"
filedbfname = Configuration.data_dir() + "/treasureobject.db"

conn = DBConnection()
cur = conn.cursor()

fields = romdb.load_field_xml(dbxmlfname)
db_fields = romdb.DBFields(cur, 'treasure', fields, [ 'dropability', 'item' ])
db_fields.recreate_table([ ('guid', 'sys_name', 'id') ])

cur.execute('DROP TABLE IF EXISTS treasure_drop ')
cur.execute("""CREATE TABLE treasure_drop (
	guid INTEGER NOT NULL,
	ordernum INTEGER NOT NULL,
	item INTEGER,
	rate INTEGER NOT NULL,
	count INTEGER NOT NULL,
	CONSTRAINT treasure_drop_ordernum_range CHECK (ordernum >= 1 AND ordernum <= 100),
	PRIMARY KEY (guid, ordernum),
	FOREIGN KEY (guid) REFERENCES treasure
)
""")
conn.commit()

records = romdb.decode_db(filedbfname, fields)
for k, v in records.items():
	if not db_fields.insert_fields(v):
		# constraint failure (no sys_name most likely) - skip the item
		continue

	for i in range(1, int(v['itemcount']) + 1):
		id = v['itemid' + str(i)]
		r = v['itemrate' + str(i)]
		c = v['itemcount' + str(i)]
		cur.execute('INSERT INTO treasure_drop (guid, ordernum, item, rate, count) VALUES (%s,%s,%s,%s,%s)', (k, i, id, r, c))
	conn.commit()

conn.close()

# vim:set ts=2 sw=2:
