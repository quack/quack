#!/bin/bash

function copy_icon {
    local SIZE=$1
    cp icons/$SIZE/application-x-quack.png /usr/share/icons/hicolor/$SIZE/mimetypes
}

# Copy mimetypes
cp mimetypes/quack.xml /usr/share/mime/packages

copy_icon '16x16'
copy_icon '32x32'
copy_icon '48x48'
copy_icon '64x64'
copy_icon '128x128'

# Reload configurations
update-mime-database /usr/share/mime
update-icon-caches /usr/share/icons/*
