
BUILD_FOLDER=../Builds
PLUGIN_FOLDER=../wp-plugin
THEME_FOLDER=../wp-theme

all: build_zips

upload_local: build_zips
	./upload_local.sh

upload: build_zips
	./upload.sh

build_zips: PurdueCruTheme.zip PurdueCruPlugin.zip

PurdueCruTheme.zip: builds $(wildcard $(THEME_FOLDER)/PurdueCruTheme/*) $(THEME_FOLDER)/PurdueCruTheme/style.css
	cd $(THEME_FOLDER); zip -r PurdueCruTheme PurdueCruTheme; mv "PurdueCruTheme.zip" $(BUILD_FOLDER)   

PurdueCruPlugin.zip: builds $(wildcard $(PLUGIN_FOLDER)/PurdueCruPlugin/*) 
	cd $(PLUGIN_FOLDER); zip -r PurdueCruPlugin PurdueCruPlugin; mv "PurdueCruPlugin.zip" $(BUILD_FOLDER)

builds:
	if [ ! -d $(BUILD_FOLDER) ]; then mkdir $(BUILD_FOLDER); fi

clean:
	rm -f $(BUILD_FOLDER)/PurdueCruTheme.zip $(BUILD_FOLDER)/PurdueCruPlugin.zip

.PHONY: builds
.PHONY: clean
    
    


    
