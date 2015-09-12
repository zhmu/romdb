#ifndef __CRC32_H__
#define __CRC32_H__

#include <stdint.h>

/*! \brief Class to aid in computing CRC-32 values
 *
 *  Based on code from http://www.libpng.org/pub/png/spec/1.2/png-1.2-pdg.html#CRCAppendix
 */
class CRC32 {
public:
	//! \brief Returns the initial CRC value
	static uint32_t InitCRC();

	//! \brief Returns the finalized CRC value
	static uint32_t FinalizeCRC(uint32_t crc);

	//! \brief Updates the given CRC with length bytes of buf
	static uint32_t UpdateCRC32(uint32_t crc, const void* buf, unsigned int length);

protected:
	//! \brief Creates the CRC32 table
	static void CreateTable();

	//! \brief Is our CRC table valid?
	static bool s_CRCTableValid;

	//! \brief CRC table to speed up calculation
	static uint32_t s_CRCTable[256];
};

#endif /* __CRC32_H__ */
