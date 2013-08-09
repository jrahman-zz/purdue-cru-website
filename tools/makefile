

BUILD_FOLDER=/home/jprahman/Dropbox/CRU_Website/Builds
PLUGIN_FOLDER=/home/jprahman/Dropbox/CRU_Website/wp-plugin
THEME_FOLDER=/home/jprahman/Dropbox/CRU_Website/wp-theme

all: build_zips

build_zips: PurdueCruTheme.zip PurdueCruPlugin.zip

PurdueCruTheme.zip: builds $(wildcard $(THEME_FOLDER)/*)
	cd $(THEME_FOLDER); zip -r PurdueCruTheme *; mv "PurdueCruTheme.zip" $(BUILD_FOLDER)   

PurdueCruPlugin.zip: builds $(wildcard $(PLUGIN_FOLDER)/*)
	cd $(PLUGIN_FOLDER); zip -r PurdueCruPlugin *; mv "PurdueCruPlugin.zip" $(BUILD_FOLDER)

builds:
	if [ ! -d $(BUILD_FOLDER) ]; then mkdir $(BUILD_FOLDER); fi

clean:
	rm -f $(BUILD_FOLDER)/PurdueCruTheme.zip $(BUILD_FOLDER)/PurdueCruPlugin.zip  

.PHONY: all
.PHONY: builds
.PHONY: clean
    
    


    
