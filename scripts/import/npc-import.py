#!/usr/bin/env python3

import os, sys
sys.path.append(os.path.join(os.path.dirname(__file__), '..', 'lib'))
import romdb
from config import Configuration
from dbconnection import DBConnection

dbxmlfname = Configuration.xml_dir() + "/db_npcobject.xml"
filedbfname = Configuration.data_dir() + "/npcobject.db"

conn = DBConnection()
cur = conn.cursor()

fields = romdb.load_field_xml(dbxmlfname)

# construct db table
db_fields = romdb.DBFields(cur, 'npc', fields, [ 'drop', 'eqtype', 'eqextype', 'spell' ])
db_fields.recreate_table([ ('guid', 'sys_name', 'id') ])

cur.execute('DROP TABLE IF EXISTS npc_drop ')
cur.execute("""CREATE TABLE npc_drop (
	guid INTEGER NOT NULL,
	ordernum INTEGER NOT NULL,
	rate INTEGER NOT NULL,
	dropid INTEGER NOT NULL,
	CONSTRAINT npc_drop_ordernum_range CHECK (ordernum >= 1 AND ordernum <= 15),
	PRIMARY KEY (guid, ordernum),
	FOREIGN KEY (guid) REFERENCES npc
)
""")

cur.execute('DROP TABLE IF EXISTS npc_weareq ')
cur.execute("""CREATE TABLE npc_weareq (
	guid INTEGER NOT NULL,
	ordernum INTEGER NOT NULL,
	typeid INTEGER NOT NULL,
	value INTEGER NOT NULL,
	CONSTRAINT npc_weareq_ordernum_range CHECK (ordernum >= 1 AND ordernum <= 10),
	PRIMARY KEY (guid, ordernum),
	FOREIGN KEY (guid) REFERENCES npc,
	FOREIGN KEY (typeid) REFERENCES sys_weareqtype (id)
)
""")

cur.execute('DROP TABLE IF EXISTS npc_eqex ')
cur.execute("""CREATE TABLE npc_eqex (
	guid INTEGER NOT NULL,
	ordernum INTEGER NOT NULL,
	typeid INTEGER NOT NULL,
	value INTEGER NOT NULL,
	CONSTRAINT npc_eqex_ordernum_range CHECK (ordernum >= 1 AND ordernum <= 10),
	PRIMARY KEY (guid, ordernum),
	FOREIGN KEY (guid) REFERENCES npc,
	FOREIGN KEY (typeid) REFERENCES sys_weareqtype (id)
)
""")

cur.execute('DROP TABLE IF EXISTS npc_spell ')
cur.execute("""CREATE TABLE npc_spell (
	guid INTEGER NOT NULL,
	ordernum INTEGER NOT NULL,
	righttime INTEGER NOT NULL,
	target INTEGER NOT NULL,
	rate INTEGER NOT NULL,
	magic INTEGER NOT NULL,
	magiclv INTEGER NOT NULL,
	string INTEGER NOT NULL,
	CONSTRAINT npc_spell_ordernum_range CHECK (ordernum >= 1 AND ordernum <= 8),
	PRIMARY KEY (guid, ordernum),
	FOREIGN KEY (guid) REFERENCES npc
)
""")
conn.commit()

records = romdb.decode_db(filedbfname, fields)
for k, v in records.items():
	if not db_fields.insert_fields(v):
		# constraint failure (no sys_name most likely) - skip the item
		continue

	# dropability todo ?

	for i in range(1, 16):
		dropid = v['dropid' + str(i)]
		droprate = v['droprate' + str(i)]
		cur.execute('INSERT INTO npc_drop (guid, ordernum, dropid, rate) VALUES (%s,%s,%s,%s)', (k, i, dropid, droprate))

	for i in range(1, 11):
		typeid = v['eqtype' + str(i)]
		typeval = v['eqtypevalue' + str(i)]
		if typeid != 0:
			cur.execute('INSERT INTO npc_weareq (guid, ordernum, typeid, value) VALUES (%s,%s,%s,%s)', (k, i, typeid, typeval))

	for i in range(1, 11):
		typeid = v['eqextype' + str(i)]
		typeval = v['eqextypevalue' + str(i)]
		if typeid != 0 and typeid != 217 and typeid != 218 and typeid != 219:
			cur.execute('INSERT INTO npc_eqex (guid, ordernum, typeid, value) VALUES (%s,%s,%s,%s)', (k, i, typeid, typeval))

	for i in range(1, 9):
		righttime = v['spellrighttime' + str(i)]
		target = v['spelltarget' + str(i)]
		rate = v['spellrate' + str(i)]
		magic = v['spellmagic' + str(i)]
		magiclv = v['spellmagiclv' + str(i)]
		string = v['spellstring' + str(i)]
		if magic != 0:
			cur.execute('INSERT INTO npc_spell (guid, ordernum, righttime, target, rate, magic, magiclv, string) VALUES (%s,%s,%s,%s,%s,%s,%s,%s)', (k, i, righttime, target, rate, magic, magiclv, string))
	conn.commit()

conn.close()


# vim:set ts=2 sw=2:
