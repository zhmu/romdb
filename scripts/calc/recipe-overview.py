#!/usr/bin/env python3

import os, sys
sys.path.append(os.path.join(os.path.dirname(__file__), '..', 'lib'))
import romdb
from dbconnection import DBConnection

conn = DBConnection()
cur = conn.cursor()

cur.execute('DROP TABLE IF EXISTS craftable_item')
cur.execute("""CREATE TABLE craftable_item(
	guid INTEGER NOT NULL,
	recipe INTEGER NOT NULL,
	FOREIGN KEY (guid) REFERENCES sys_name (id),
	FOREIGN KEY (recipe) REFERENCES recipe (guid)
)
""")

# recipe names do not have to exist in the sys_name table; to cope, we'll have a look at all craftable items
# and generate a 'craftable_item' record for it
cur.execute("SELECT DISTINCT i.guid,i.item FROM recipe_itemslot i,sys_name sn WHERE i.item=sn.id")
b = cur.fetchall()
for guid, item in b:
	cur.execute("INSERT INTO craftable_item (guid,recipe) VALUES (%s,%s)" % (item, guid))

conn.commit()
conn.close()
