#ifndef __PNG_H__
#define __PNG_H__

#include <stdint.h>
#include <stdlib.h>
#include "fdb.h"

/*! \brief Class used to write PNG files
 */
class PNG {
public:
	/*! \brief Writes the PNG file data from entry/data to a file
	 *  \param entry FDB entry information
	 *  \param data Decoded PNG image data
	 *  \param datalen Length of decoded PNG data
	 *  \returns true on success
	 */
	static bool Write(FILE* f, const FDB::Entry& entry, const char* data, int datalen);

protected:
	//! \brief Writes a single chunk of a given type to a file
	static bool WriteChunk(FILE* f, const char* type, const void* data, int length);	
};

#endif /* __CRC32_H__ */
