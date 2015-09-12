#ifndef __DDSFILE_H__
#define __DDSFILE_H__

#include <stdint.h>

#define PACKED __attribute__((packed))

namespace DDSFile {
#define DDS_MAGIC 0x20534444	/* 'DDS ' */

	/*! \brief Pixel format
	 *
	 *  Based on http://msdn.microsoft.com/en-us/library/bb943984(v=vs.85).aspx
	 */
	struct PixelFormat {
		uint32_t	pf_size;
		uint32_t	pf_flags;
#define DDS_PIXELFORMAT_ALPHAPIXELS	0x00000001
#define DDS_PIXELFORMAT_ALPHA		0x00000002
#define DDS_PIXELFORMAT_FOURCC		0x00000004
#define DDS_PIXELFORMAT_RGB		0x00000040
#define DDS_PIXELFORMAT_YUV		0x00000200
#define DDS_PIXELFORMAT_LUMINANCE	0x00020000
		uint32_t	pf_fourcc;
#define DDS_PIXELFORMAT_FOURCC_DXT1	('D' | 'X' << 8 | 'T' << 16 | '1' << 24)
#define DDS_PIXELFORMAT_FOURCC_DXT5	('D' | 'X' << 8 | 'T' << 16 | '5' << 24)
		uint32_t	pf_rgb_bitcount;
		uint32_t	pf_r_bitmask;
		uint32_t	pf_g_bitmask;
		uint32_t	pf_b_bitmask;
		uint32_t	pf_a_bitmask;
	};

	/*! \brief Header
	 *
	 *  Based on http://msdn.microsoft.com/en-us/library/bb943982(v=vs.85).aspx
	 */
	struct Header {
		uint32_t	h_size;
		uint32_t	h_flags;
#define DDS_HEADER_FLAGS_CAPS		0x00000001
#define DDS_HEADER_FLAGS_HEIGHT		0x00000002
#define DDS_HEADER_FLAGS_WIDTH		0x00000004
#define DDS_HEADER_FLAGS_PITCH		0x00000008
#define DDS_HEADER_FLAGS_PIXELFORMAT	0x00001000
#define DDS_HEADER_FLAGS_MIPMAPCOUNT	0x00020000
#define DDS_HEADER_FLAGS_LINEARSIZE	0x00080000
#define DDS_HEADER_FLAGS_DEPTH		0x00800000
		uint32_t	h_height;
		uint32_t	h_width;
		uint32_t	h_pitch_or_linear_size;
		uint32_t	h_depth;
		uint32_t	h_mipmap_count;
		uint32_t	h_reserved[11];
		struct PixelFormat h_pixelformat;
		uint32_t	h_caps;
#define DDS_HEADER_CAPS_COMPLEX		0x00000008
#define DDS_HEADER_CAPS_MIPMAP		0x00400000
#define DDS_HEADER_CAPS_TEXTURE		0x00001000
		uint32_t	h_caps2;
		uint32_t	h_caps3;
		uint32_t	h_caps4;
		uint32_t	h_reserved2;
	};
};

#endif /* __DDSFILE_H__ */
