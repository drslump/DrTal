#!/bin/sh

TempDir=/tmp/natdocs
NatDocs=/var/www/natdocs

ProjectDir=/var/www/projects/drtal
SourceDir=$ProjectDir/lib
DocsDir=$ProjectDir/docs
ConfigDir=$ProjectDir/build/docs

mkdir $TempDir

rm -rf $DocsDir

perl $NatDocs/NaturalDocs -r -i $SourceDir -o HTML $TempDir -p $ConfigDir -cs UTF-8

php docs/generator.php --input=$TempDir --output=$DocsDir

#rm -rf $TempDir
