#!/usr/bin/env python3

import os, sys
sys.path.append(os.path.join(os.path.dirname(__file__), '..', 'lib'))
import romdb
from config import Configuration
from dbconnection import DBConnection

dbxmlfname = Configuration.xml_dir() + "/db_recipeobject.xml"
filedbfname = Configuration.data_dir() + "/recipeobject.db"

conn = DBConnection()
cur = conn.cursor()

fields = romdb.load_field_xml(dbxmlfname)

db_fields = romdb.DBFields(cur, 'recipe', fields, [ 'source' ])
db_fields.recreate_table([ ])

cur.execute('DROP TABLE IF EXISTS recipe_source ')
cur.execute("""CREATE TABLE recipe_source (
	guid INTEGER NOT NULL,
	ordernum INTEGER NOT NULL,
	source INTEGER NOT NULL,
	count INTEGER NOT NULL,
	reduce INTEGER NOT NULL,
	CONSTRAINT recipe_source_ordernum_range CHECK (ordernum >= 1 AND ordernum <= 8),
	PRIMARY KEY (guid, ordernum),
	FOREIGN KEY (guid) REFERENCES recipe
)
""")

cur.execute('DROP TABLE IF EXISTS recipe_itemslot ')
cur.execute("""CREATE TABLE recipe_itemslot (
	guid INTEGER NOT NULL,
	ordernum INTEGER NOT NULL,
	item INTEGER NOT NULL,
	count INTEGER NOT NULL,
	rate INTEGER NOT NULL,
	CONSTRAINT recipe_itemslot_ordernum_range CHECK (ordernum >= 1 AND ordernum <= 4),
	PRIMARY KEY (guid, ordernum),
	FOREIGN KEY (guid) REFERENCES recipe
)
""")

records = romdb.decode_db(filedbfname, fields)
for k, v in records.items():
	if not db_fields.insert_fields(v):
		# constraint failure (no sys_name most likely) - skip the item
		continue

	for i in range(1, 9):
		sid = v['source' + str(i)]
		scount = v['sourcecount' + str(i)]
		sreduce = v['sourcereduce' + str(i)]
		if sid != 0:
			cur.execute('INSERT INTO recipe_source (guid, ordernum, source, count, reduce) VALUES (%s,%s,%s,%s,%s)', (k, i, sid, scount, sreduce))

	for i in range(1, 5):
		s = v['item1_slot' + str(i)];
		c = v['item1_slot' + str(i) + 'count'];
		r = v['item1_slot' + str(i) + 'rate'];
		if s != 0:
			cur.execute('INSERT INTO recipe_itemslot (guid, ordernum, item, count, rate) VALUES (%s,%s,%s,%s,%s)', (k, i, s, c, r))
	conn.commit()

conn.close()


# vim:set ts=2 sw=2:
