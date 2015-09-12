#include "png.h"
#include <arpa/inet.h>
#include <assert.h>
#include <string.h>
#include <zlib.h>
#include "crc32.h"
#include "pngfile.h"

bool
PNG::WriteChunk(FILE* f, const char* type, const void* data, int length)
{
	PNGFile::ChunkPrefix cp;
	cp.cp_length = htonl(length);
	memcpy(&cp.cp_type[0], type, sizeof(PNGFile::ChunkPrefix::cp_type));

	uint32_t crc = CRC32::InitCRC();
	crc = CRC32::UpdateCRC32(crc, type, sizeof(PNGFile::ChunkPrefix::cp_type));
	crc = CRC32::UpdateCRC32(crc, data, length);
	crc = CRC32::FinalizeCRC(crc);

	PNGFile::ChunkSuffix cs;
	cs.cs_crc = htonl(crc);
	return fwrite(&cp, sizeof(cp), 1, f) && (length == 0 || fwrite(data, length, 1, f)) && fwrite(&cs, sizeof(cs), 1, f);
}

bool
PNG::Write(FILE* f, const FDB::Entry& entry, const char* data, int datalen)
{
	// Sanity check
	if (datalen != entry.GetTextureHeight() * entry.GetTextureWidth() * 4) {
		fprintf(stderr, "wrong size\n");
		return false;
	}

	// First step is the magic signature identifying this file
	{
		uint32_t sig1 = PNGFile::Signature1, sig2 = PNGFile::Signature2;
		if (!fwrite(&sig1, sizeof(sig1), 1, f) || !fwrite(&sig2, sizeof(sig2), 1, f))
			return false;
	}

	// Write the image header chunk
	{
		struct PNGFile::IHDR ihdr;
		memset(&ihdr, 0, sizeof(ihdr));
		ihdr.ih_width = htonl(entry.GetTextureWidth());
		ihdr.ih_height = htonl(entry.GetTextureHeight());
		ihdr.ih_bitdepth = 8;
		ihdr.ih_colortype = PNG_IHDR_COLORTYPE_RGBA;
		ihdr.ih_compression = PNG_IHDR_COMPRESSION_ZLIB;
		ihdr.ih_filter = PNG_IHDR_FILTER_METHOD_0;
		ihdr.ih_interlace = PNG_IHDR_INTERLACE_NONE;
		if (!WriteChunk(f, "IHDR", &ihdr, sizeof(ihdr)))
			return false;
	}

	// Write the image data chunk - this involves recompression the data
	{
		z_stream strm;
		strm.zalloc = Z_NULL;
		strm.zfree =  Z_NULL;
		strm.opaque = Z_NULL;
		if (deflateInit(&strm, Z_DEFAULT_COMPRESSION) != Z_OK)
			return false;

		// XXX We assume the output will not be longer than the input
		uint8_t* in = new uint8_t[entry.GetTextureWidth() * 4 + 1];
		uint8_t* out = new uint8_t[datalen];

		strm.next_out = out;
		strm.avail_out = datalen;
		const uint8_t* cur_ptr = (const uint8_t*)data;
		bool ok = true;
		for (int n = 0; ok && n < entry.GetTextureHeight(); n++) {
			// Construct the scanline data to compress; first is the filter, next is the data
			in[0] = PNG_FILTER_NONE;
			memcpy(&in[1], cur_ptr, entry.GetTextureWidth() * 4);
			cur_ptr += entry.GetTextureWidth() * 4;

			// Compress a single scanline
			strm.next_in = (Bytef*)in;
			strm.avail_in = entry.GetTextureWidth() * 4 + 1;
			ok = deflate(&strm, (n == entry.GetTextureHeight() - 1) ? Z_FINISH : Z_NO_FLUSH) != Z_STREAM_ERROR;
		}
		deflateEnd(&strm);
		ok = ok && WriteChunk(f, "IDAT", out, datalen - strm.avail_out);

		delete[] out;
		delete[] in;
		if (!ok)
			return false;
	}
	return WriteChunk(f, "IEND", NULL, 0);
}

/* vim:set ts=2 sw=2: */
