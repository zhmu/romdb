#!/usr/bin/env python3

import os, sys
sys.path.append(os.path.join(os.path.dirname(__file__), '..', 'lib'))
import re
import romdb
from config import Configuration
from dbconnection import DBConnection
import subprocess

dbxmlfname = Configuration.xml_dir() + "/db_imageobject.xml"
filedbfname = Configuration.data_dir() + "/imageobject.db"

conn = DBConnection()
cur = conn.cursor()

img_root = "/tmp/"

cur.execute("SELECT guid,actfield FROM image")
b = cur.fetchall()
for guid, actfield in b:
	if not actfield:
		continue
	img = actfield.replace("\\", "/").lower()

	# ensure img contains only characters that we accept
	if not re.match("[a-z0-9_/]+", img):
		print("*** Rejecting '%s'" % img)
		continue

	# if the image does not end with .dds, add it
	if not img.endswith(".dds"):
		img += '.dds'

	# got it; try to convert the image
	if subprocess.call(["convert", img_root + img, "/tmp/" + str(guid) + ".png"]) != 0:
		print("* Failure converting %s" % img)

#for i in *.png; do convert -resize 40x40 $i ../small/$i; done    

