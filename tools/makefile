

BUILD_FOLDER=/home/jprahman/Dropbox/CRU_Website/Builds
PLUGIN_FOLDER=/home/jprahman/Dropbox/CRU_Website/wp-plugin
THEME_FOLDER=/home/jprahman/Dropbox/CRU_Website/wp-theme

all: build_zips

build_zips: PurdueCruTheme.zip PurdueCruPlugin.zip

PurdueCruTheme.zip: builds
	zip -r PurdueCruTheme $(THEME_FOLDER); mv "PurdueCruTheme.zip" $(BUILD_FOLDER)   

PurdueCruPlugin.zip: builds
	zip -r PurdueCruPlugin $(PLUGIN_FOLDER); mv "PurdueCruPlugin.zip" $(BUILD_FOLDER)

builds:
	mkdir $(BUILD_FOLDER)

clean:
    

.PHONY: all
.PHONY: build_zips
.PHONY: clean
    
    


    
