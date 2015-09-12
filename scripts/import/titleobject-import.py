#!/usr/bin/env python3

import os, sys
sys.path.append(os.path.join(os.path.dirname(__file__), '..', 'lib'))
import romdb
from config import Configuration
from dbconnection import DBConnection

dbxmlfname = Configuration.xml_dir() + "/db_titleobject.xml"
filedbfname = Configuration.data_dir() + "/titleobject.db"

conn = DBConnection()
cur = conn.cursor()

fields = romdb.load_field_xml(dbxmlfname)

# construct db table
db_fields = romdb.DBFields(cur, 'title', fields, [ 'eqtype' ])
db_fields.recreate_table([ ('guid', 'sys_name', 'id') ])

cur.execute('DROP TABLE IF EXISTS title_weareq ')
cur.execute("""CREATE TABLE title_weareq (
	guid INTEGER NOT NULL,
	ordernum INTEGER NOT NULL,
	typeid INTEGER NOT NULL,
	value INTEGER NOT NULL,
	CONSTRAINT title_weareq_ordernum_range CHECK (ordernum >= 1 AND ordernum <= 5),
	PRIMARY KEY (guid, ordernum),
	FOREIGN KEY (guid) REFERENCES title,
	FOREIGN KEY (typeid) REFERENCES sys_weareqtype (id)
)
""")

conn.commit()

records = romdb.decode_db(filedbfname, fields)
for k, v in records.items():
	if not db_fields.insert_fields(v):
		# constraint failure (no sys_name most likely) - skip the item
		continue

	for i in range(1, 6):
		typeid = v['eqtype' + str(i)]
		typeval = v['eqtypevalue' + str(i)]
		if typeid != 0:
			cur.execute('INSERT INTO title_weareq (guid, ordernum, typeid, value) VALUES (%s,%s,%s,%s)', (k, i, typeid, typeval))
	conn.commit()

conn.close()

# vim:set ts=2 sw=2:
