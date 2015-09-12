#!/usr/bin/env python3

import os, sys
sys.path.append(os.path.join(os.path.dirname(__file__), '..', 'lib'))
import romdb
from config import Configuration
from dbconnection import DBConnection

dbxmlfname = Configuration.xml_dir() + "/db_eqrefineabilityobject.xml"
filedbfname = Configuration.data_dir() + "/eqrefineabilityobject.db"

conn = DBConnection()
cur = conn.cursor()

fields = romdb.load_field_xml(dbxmlfname)

# construct db table
db_fields = romdb.DBFields(cur, 'refine', fields, [ 'eqtype' ])
db_fields.recreate_table([ ])

cur.execute('DROP TABLE IF EXISTS refine_prop')
cur.execute("""CREATE TABLE refine_prop (
	guid INTEGER NOT NULL,
	ordernum INTEGER NOT NULL,
	typeid INTEGER NOT NULL,
	value INTEGER NOT NULL,
	CONSTRAINT refine_prop_ordernum_range CHECK (ordernum >= 1 AND ordernum <= 10),
	PRIMARY KEY (guid, ordernum),
	FOREIGN KEY (guid) REFERENCES refine,
	FOREIGN KEY (typeid) REFERENCES sys_weareqtype (id)
)
""")

records = romdb.decode_db(filedbfname, fields)
for k, v in records.items():
	if not db_fields.insert_fields(v):
		raise Exception

	for i in range(1, 11):
		typeid = v['eqtype' + str(i)]
		typeval = v['eqtypevalue' + str(i)]
		if typeid != 0:
			cur.execute('INSERT INTO refine_prop (guid, ordernum, typeid, value) VALUES (%s,%s,%s,%s)', (k, i, typeid, typeval))

conn.commit()
conn.close()


# vim:set ts=2 sw=2:
