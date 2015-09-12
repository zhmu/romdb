CREATE VIEW all_imageid AS
	  SELECT guid,imageid FROM armor
UNION ALL SELECT guid,imageid FROM weapon
UNION ALL SELECT guid,imageid FROM item;
