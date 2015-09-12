#!/usr/bin/env python3

import os, sys
sys.path.append(os.path.join(os.path.dirname(__file__), '..', 'lib'))
import romdb
from dbconnection import DBConnection

conn = DBConnection()
cur = conn.cursor()

def get_treasure_drops(guid, tc_rate):
	cur = conn.cursor()

	# fetch total rate; we need to divide by this. I've only seen 75000, 80000 and 100000 (most common)
	# the other may be bugs due to missing items?
	cur.execute("SELECT SUM(rate) FROM treasure_drop WHERE guid=%s", (guid, ))
	total_rate = cur.fetchone()[0]

	cur.execute("SELECT ordernum,item,rate,count FROM treasure_drop WHERE guid=%s", (guid, ))
	a = cur.fetchall()
	result = []
	for r in a:
		(ordernum, item, rate, count) = r
		if item >= 720000: # treasure id
			result.append(get_treasure_drops(item, tc_rate * (rate / total_rate)))
		else: # item
			result.append(( item, count, tc_rate * (rate / total_rate)))
	return result

# flattens list-in-list to a single list with all elements
def squash_list(l):
	result = [ ]
	for r in l:
		if isinstance(r, list):
			result.extend(squash_list(r))
		else:
			result.append(r)
	return result

cur.execute('DROP TABLE IF EXISTS npc_droplist ')
cur.execute("""CREATE TABLE npc_droplist (
	guid INTEGER NOT NULL,
	item INTEGER NOT NULL,
	dropnum INTEGER NOT NULL,
	count INTEGER NOT NULL,
	rate FLOAT NOT NULL,
	CONSTRAINT npc_droplate_rate_limit CHECK (rate >= 0 AND rate <= 1),
	PRIMARY KEY (guid, item, dropnum, count)
)
""")

cur.execute("SELECT guid FROM npc")
b = cur.fetchall()
for guid in b:
	cur.execute("SELECT ordernum,rate,dropid FROM npc_drop WHERE guid=%s", (guid, ))
	all_items =  { }
	a = cur.fetchall()
	n = 1
	for r in a:
		# for now, assume a maximum rate value; this value or above
		# will always cause a drop from the given droplist
		max_rate = 100000
		(ordernum, rate, dropid) = r
		if rate > max_rate:
			rate = max_rate

		x = get_treasure_drops(dropid, rate / max_rate)
		drop = squash_list(x)

		# sum items in this drop
		item_drops = { }
		for (i, c, rate) in drop:
			total_rate = [ r for (item, count, r ) in drop if item == i and count == c]
			if (i, c) in item_drops:
				pass;
			else:
				item_drops[( i, c )] =  sum(total_rate)
		all_items[n] = item_drops

		n = n + 1

	for n, item_drops in all_items.items():
		for (item, count) in item_drops.keys():
			rate = item_drops[(item, count)]
			if rate > 1.0:
				print("*** Warning: npc %s item %s has rate %s, fixing" % (guid, item, rate))
				rate = 1.0
			cur.execute("INSERT INTO npc_droplist (guid, item, dropnum, count, rate) VALUES (%s, %s,%s,%s,%s)", (guid, item, n, count, rate))

conn.commit()
conn.close()
