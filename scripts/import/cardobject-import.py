#!/usr/bin/env python3

import os, sys
sys.path.append(os.path.join(os.path.dirname(__file__), '..', 'lib'))
import romdb
from config import Configuration
from dbconnection import DBConnection

dbxmlfname = Configuration.xml_dir() + "/db_cardobject.xml"
filedbfname = Configuration.data_dir() + "/cardobject.db"

conn = DBConnection()
cur = conn.cursor()

fields = romdb.load_field_xml(dbxmlfname)

db_fields = romdb.DBFields(cur, 'card', fields, [ ])
db_fields.recreate_table([ ('cardaddpower', 'addpower', 'guid') ])
conn.commit()

records = romdb.decode_db(filedbfname, fields)
for k, v in records.items():
	if not db_fields.insert_fields(v):
		# constraint failure (no sys_name most likely) - skip the item
		continue
	conn.commit()

conn.close()

# vim:set ts=2 sw=2:
