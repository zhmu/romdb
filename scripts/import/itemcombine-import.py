#!/usr/bin/env python3

import os, sys
sys.path.append(os.path.join(os.path.dirname(__file__), '..', 'lib'))
import romdb
from config import Configuration
from dbconnection import DBConnection

dbxmlfname = Configuration.xml_dir() + "/db_itemcombine.xml"
filedbfname = Configuration.data_dir() + "/itemcombine.db"

conn = DBConnection()
cur = conn.cursor()

fields = romdb.load_field_xml(dbxmlfname)

db_fields = romdb.DBFields(cur, 'item_combine', fields, [ 'src' ])
db_fields.recreate_table([ ('dstitem', 'sys_name', 'id') ])

cur.execute('DROP TABLE IF EXISTS item_combine_src ')
cur.execute("""CREATE TABLE item_combine_src (
	guid INTEGER NOT NULL,
	ordernum INTEGER NOT NULL,
	item INTEGER NOT NULL,
	amount INTEGER NOT NULL,
	CONSTRAINT item_combine_src_ordernum_range CHECK (ordernum >= 1 AND ordernum <= 3),
	CONSTRAINT item_combine_src_amount_range CHECK (amount >= 1),
	PRIMARY KEY (guid, ordernum),
	FOREIGN KEY (guid) REFERENCES item_combine
)""")
conn.commit()

records = romdb.decode_db(filedbfname, fields)
for k, v in records.items():
	if not db_fields.insert_fields(v):
		# constraint failure (no sys_name most likely) - skip the item
		continue

	for num in range(1, 4):
		srcitem = v['srcitem' + str(num)]
		srccount = v['srccount' + str(num)]
		if srccount != 0:
			cur.execute('INSERT INTO item_combine_src (guid, ordernum, item, amount) VALUES (%s, %s, %s, %s)', (k, num, srcitem, srccount))
	conn.commit()

conn.close()

# vim:set ts=2 sw=2:
