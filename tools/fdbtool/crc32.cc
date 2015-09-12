#include "crc32.h"

/*
 * The CRC32-code is takne from appendix 15: Sample CRC Code in
 * http://www.libpng.org/pub/png/spec/1.2/png-1.2-pdg.html#CRCAppendix
 */
bool CRC32::s_CRCTableValid = false;
uint32_t CRC32::s_CRCTable[256];
   
void
CRC32::CreateTable()
{
	for (unsigned int n = 0; n < 256; n++) {
		uint32_t c = (uint32_t)n;
		for (unsigned int k = 0; k < 8; k++) {
			if (c & 1)
				c = 0xedb88320L ^ (c >> 1);
			else
				c = c >> 1;
		}
		s_CRCTable[n] = c;
	}
	s_CRCTableValid = true;
}

uint32_t
CRC32::UpdateCRC32(uint32_t crc, const void* buf, unsigned int length)
{
	const uint8_t* data = (const uint8_t*)buf;

	if (!s_CRCTableValid)
		CreateTable();

	uint32_t c = crc;
	for (unsigned int n = 0; n < length; n++)
		c = s_CRCTable[(c ^ data[n]) & 0xff] ^ (c >> 8);
	return c;
}

uint32_t
CRC32::InitCRC()
{
	return 0xffffffffL;
}

uint32_t
CRC32::FinalizeCRC(uint32_t crc)
{
	return crc ^ 0xffffffffL;
}

/* vim:set ts=2 sw=2: */
