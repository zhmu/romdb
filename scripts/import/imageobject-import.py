#!/usr/bin/env python3

import os, sys
sys.path.append(os.path.join(os.path.dirname(__file__), '..', 'lib'))
import romdb
from config import Configuration
from dbconnection import DBConnection

dbxmlfname = Configuration.xml_dir() + "/db_imageobject.xml"
filedbfname = Configuration.data_dir() + "/imageobject.db"

conn = DBConnection()
cur = conn.cursor()

fields = romdb.load_field_xml(dbxmlfname)

# construct db table
db_fields = romdb.DBFields(cur, 'image', fields, [ ])
db_fields.recreate_table([ ])

records = romdb.decode_db(filedbfname, fields)
for k, v in records.items():
	if not db_fields.insert_fields(v):
		raise Exception

conn.commit()
conn.close()

# vim:set ts=2 sw=2:
