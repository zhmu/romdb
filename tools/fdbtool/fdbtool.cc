#include <sys/types.h>
#include <sys/stat.h>
#include <assert.h>
#include <errno.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include "bmpfile.h"
#include "ddsfile.h"
#include "fdb.h"
#include "png.h"
#include "tgafile.h"

int g_verbosity = 0;

// Transforms \ -> / in 'in' and returns the result (caller free's)
static char*
transform_slashes(const char* in)
{
	char* out = strdup(in);
	while(1) {
		char* ptr = strchr(out, '\\');
		if (ptr == NULL)
			break;
		*ptr = '/';
	}
	return out;
}

// Given 'a/b/c/filename' and prefix 'prefix' creates 'prefix/a/b/c/filename' as full and
// extracts the directory. (prefix/a/b/c) and filename pieces. directory/full must
// be freed by the caller
void
make_directory_filename(const char* in, const char* prefix, char*& directory, char*& filename, char*& full)
{
	full = (char*)malloc(strlen(in) + 1 /* / */ + strlen(prefix) + 1 /* \0 */);
	sprintf(full, "%s/%s", prefix, in);
	directory = (char*)malloc(strlen(in) + 1 /* / */ + strlen(prefix) + 1 /* \0 */);
	strcpy(directory, full);
	char* ptr = strrchr(directory, '/');
	if (ptr != NULL) {
		*ptr = '\0';
		filename = ptr + 1;
	} else {
		filename = NULL;
	}
}

// Creates a directory and all its subdirectories (i.e. a/b/c will create a,
// a/b and a/b/c as needed)
void
make_directory(const char* directory)
{
	char piece[1024];
	assert(strlen(directory) < sizeof(piece));

	memset(piece, 0, sizeof(piece));
	int cur_pos = 0;
	const char* ptr;
	do {
		ptr = strchr(directory + cur_pos, '/');
		if (ptr != NULL) {
			strncpy(piece, directory, ptr - directory);
			cur_pos = ptr - directory + 1;
		} else {
			strcpy(piece, directory);
		}

		// Only attempt to create the piece if we can't find it
		struct stat sb;
		if (stat(piece, &sb) < 0 && errno == ENOENT) {
			if (mkdir(piece, 0755) < 0)
				fprintf(stderr, "can't create directory '%s'\n", piece);
		}
	} while (ptr != NULL);
}

static bool
dds_write_header(FILE* f, const char* filename, const FDB::Entry& entry)
{
	// XXX We don't fill out everything (pitch line, texture flag - should we?)
	DDSFile::Header header;
	memset(&header, 0, sizeof(header));
	header.h_size = sizeof(header);
	header.h_flags = DDS_HEADER_FLAGS_CAPS | DDS_HEADER_FLAGS_HEIGHT | DDS_HEADER_FLAGS_WIDTH | DDS_HEADER_FLAGS_PIXELFORMAT | DDS_HEADER_FLAGS_MIPMAPCOUNT;
	header.h_height = entry.GetTextureHeight();
	header.h_width = entry.GetTextureWidth();
	header.h_mipmap_count = entry.GetTextureMipCount();
	header.h_pixelformat.pf_size = sizeof(struct DDSFile::PixelFormat);
	switch(entry.GetTextureCompression()) {
		case FDBFILE_TEXTURE_COMPRESSION_NONE:
			header.h_pixelformat.pf_flags = DDS_PIXELFORMAT_ALPHAPIXELS | DDS_PIXELFORMAT_RGB;
			header.h_pixelformat.pf_rgb_bitcount = 32;
			header.h_pixelformat.pf_r_bitmask = 0xff << 16;
			header.h_pixelformat.pf_g_bitmask = 0xff << 8;
			header.h_pixelformat.pf_b_bitmask = 0xff << 0;
			header.h_pixelformat.pf_a_bitmask = 0xff << 24;
			break;
		case FDBFILE_TEXTURE_COMPRESSION_DXT1_ALPHA:
			header.h_flags |= DDS_PIXELFORMAT_ALPHAPIXELS;
		case FDBFILE_TEXTURE_COMPRESSION_DXT1:
			header.h_pixelformat.pf_flags = DDS_PIXELFORMAT_FOURCC;
			header.h_pixelformat.pf_fourcc = DDS_PIXELFORMAT_FOURCC_DXT1;
			break;
		case FDBFILE_TEXTURE_COMPRESSION_DXT5:
			header.h_pixelformat.pf_flags = DDS_PIXELFORMAT_FOURCC;
			header.h_pixelformat.pf_fourcc = DDS_PIXELFORMAT_FOURCC_DXT5;
			break;
		default:
			fprintf(stderr, "*** Unimplemented texture compression %d in %s, skipping\n", entry.GetTextureCompression(), filename);
			return false;
	}

	// Write the magic pieces
	uint32_t magic = DDS_MAGIC;
	return fwrite(&magic, sizeof(magic), 1, f) && fwrite(&header, sizeof(header), 1, f);
}

static bool
tga_write_header(FILE* f, const char* filename, const FDB::Entry& entry)
{
	TGAFile::Header header;
	memset(&header, 0, sizeof(header));
	header.h_idfield_numchars = 0;
	header.h_colormap_type = 0;
	header.h_imagetype_code = TGA_HEADER_IMAGETYPE_UNMAPPED_RGB;
	header.h_x_origin = 0;
	header.h_y_origin = 0;
	header.h_width_image = entry.GetTextureWidth();
	header.h_height_image = entry.GetTextureHeight();
	header.h_pixel_size = 32;
	header.h_image_descriptor = TGA_HEADER_IMAGEDESCRIPTOR_ATTR_BITS(8) | TGA_HEADER_IMAGEDESCRIPTOR_ORIGIN_UPPERLEFT;
	switch(entry.GetTextureCompression()) {
		case FDBFILE_TEXTURE_COMPRESSION_NONE:
			break;
		default:
			fprintf(stderr, "*** Unimplemented texture compression %d in %s, skipping\n", entry.GetTextureCompression(), filename);
			return false;
	}

	return !!fwrite(&header, sizeof(header), 1, f);
}

static bool
bmp_write_header(FILE* f, const char* filename, const FDB::Entry& entry, unsigned int len)
{
	BMPFile::FileHeader fh;
	memset(&fh, 0, sizeof(fh));
	fh.fh_type = BMP_FILEHEADER_TTYPE;
	fh.fh_size = sizeof(BMPFile::FileHeader) + sizeof(BMPFile::InfoHeader) + len;
	fh.fh_offbits = sizeof(BMPFile::FileHeader) + sizeof(BMPFile::InfoHeader);
	switch(entry.GetTextureCompression()) {
		case FDBFILE_TEXTURE_COMPRESSION_NONE:
			break;
		default:
			fprintf(stderr, "*** Unimplemented texture compression %d in %s, skipping\n", entry.GetTextureCompression(), filename);
			return false;
	}

	if (!fwrite(&fh, sizeof(fh), 1, f))
		return false;

	BMPFile::InfoHeader ih;
	memset(&ih, 0, sizeof(ih));
	ih.ih_size = sizeof(BMPFile::InfoHeader);
	ih.ih_width = entry.GetTextureWidth();
	ih.ih_height = -entry.GetTextureHeight();
	ih.ih_planes = 1;
	ih.ih_bitcount = 32;
	ih.ih_compression = BMP_HEADER_COMPRESSION_RGB;
	ih.ih_sizeimage = len;
	return !!fwrite(&ih, sizeof(ih), 1, f);
}

static void
usage(const char* progname)
{
	fprintf(stderr, "usage: %s [-h?v] [-p path] file.fdb\n", progname);
	fprintf(stderr, "\n");
	fprintf(stderr, "  -h, -?       this help\n");
	fprintf(stderr, "  -v           increase verbosity\n");
	fprintf(stderr, "  -p path      use path as prefix when writing extracted files\n");
}

int
main(int argc, char* argv[])
{
	char* suffix = strdup(".");

	{
		int opt;
		while ((opt = getopt(argc, argv, "h?p:v")) != -1) {
			switch(opt) {
				case 'h':
				case '?':
					usage(argv[0]);
					return EXIT_FAILURE;
				case 'v':
					g_verbosity++;
					break;
				case 'p':
					free(suffix);
					suffix = strdup(optarg);
					break;
			}
		}
	}

	if (optind != argc - 1) {
		fprintf(stderr, "expected fdb filename\n");
		usage(argv[0]);
		return EXIT_FAILURE;
	}

	// Ensure the path is really a directory
	{
		struct stat sb;
		if (stat(suffix, &sb) < 0 || !S_ISDIR(sb.st_mode)) {
			fprintf(stderr, "'%s' does not exist or is not a path", suffix);
			return EXIT_FAILURE;
		}
	}

	try {
		FDB fdb;
		fdb.Load(argv[optind]);

		const FDB::TEntryVector& entries = fdb.GetEntries();
		for (FDB::TEntryVector::const_iterator it = entries.begin(); it != entries.end(); it++) {
			const FDB::Entry& entry = *it;
			char* directory;
			char* filename;
			char* full;
			{
				char* tmp = transform_slashes(entry.GetName());
				make_directory_filename(tmp, suffix, directory, filename, full);
				free(tmp);
			}

			make_directory(directory);

			if (g_verbosity > 1)
				printf("> Extracting '%s/%s'\n", directory, filename);
			try {
				bool ok = true;
				char* data;
				unsigned int len;
				entry.Read(data, len);

				FILE* f = fopen(full, "wb");
				if (f == NULL) {
					fprintf(stderr, "Can't open '%s' for writing, skipping\n", full);
					ok = false;
				}

				if (ok && entry.GetType() == FDBFILE_FILE_TYPE_TEXTURE) {
					/*
					 * This is a texture thing; this means it is a DDS file but it will not
					 * have a header (since the FDB file contains all the relevant header
					 * fields), and thus, we'll have to construct the relevant header bits.
					 */
					if (strstr(filename, ".dds") != NULL) {
						ok = ok && dds_write_header(f, filename, entry);
					} else if (strstr(filename, ".tga") != NULL) {
						ok = ok && tga_write_header(f, filename, entry);
					} else if (strstr(filename, ".png") != NULL) {
						PNG::Write(f, entry, data, len);
						ok = false; // to prevent writing more
					} else if (strstr(filename, ".bmp") != NULL) {
						ok = ok && bmp_write_header(f, filename, entry, len);
					} else {
						printf("*** Texture '%s' is neither DDS nor TGA file, skipping\n", filename);
						ok = false;
					}
				}
	
				if (ok) {
					fwrite(data, len, 1, f);
				}

				if (f != NULL)
					fclose(f);

				delete[] data;
			} catch (FDB::Exception& e) {
				printf("can't extract '%s/%s': %s\n", directory, filename, e.what());
			}
			free(full);
			free(directory);
		}
	} catch (FDB::Exception& e) {
		printf("fdb.load(): %s\n", e.what());
		return EXIT_FAILURE;
	}

	free(suffix);
	return EXIT_SUCCESS;
}

/* vim:set ts=2 sw=2: */
