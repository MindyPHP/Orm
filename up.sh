#!/bin/bash

update() {
    cd $1
    echo "Run git pull in: $1"
    git pull origin master
    cd $2
}

for f in vendor/mindy/* ; do
    if [ -d "$f" ]; then
        update $f `pwd`
    fi
done
