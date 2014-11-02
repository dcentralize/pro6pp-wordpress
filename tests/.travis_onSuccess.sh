#!/bin/bash

if [ "$TRAVIS_PULL_REQUEST" == "true" ];
then
     exit 0;
fi

echo -e "Starting to update stable branch.\n"

#Copy data we're interested in to other place
mkdir -p $HOME/pluginFiles
cp -R $REPO_BASE/js $HOME/pluginFiles/
cp -R $REPO_BASE/templates $HOME/pluginFiles/
cp $REPO_BASE/pro6pp.php $HOME/pluginFiles/
cp $REPO_BASE/pro6pp_autocomplete.php $HOME/pluginFiles/
cp $REPO_BASE/settings.php $HOME/pluginFiles/
cp $REPO_BASE/README.md $HOME/pluginFiles/

#Go to home and setup git
cd $HOME
git config --global user.email "travis@travis-ci.org"
git config --global user.name "Travis"

#Using token, clone stable branch
git clone --quiet --branch=stable https://${GH_TOKEN}@github.com/dcentralize/pro6pp-wordpress.git  stable > /dev/null

#Go into directory and copy data we're interested in to that directory
# Hack the repository and empty all unecessary files if any.
mkdir -p tmp_branch;
cp -r ./stable/* ./tmp_branch/
rm -r ./stable/*
rm ./stable/.travis.yml
rm ./stable/.gitignore
cp -r ./tmp_branch/.git ./stable/
cd stable;
cp -Rf $HOME/pluginFiles/* .

#Add, commit and push files
git add -Af .
git commit -m "Travis build $TRAVIS_BUILD_NUMBER pushed to stable branch."
git push -fq origin stable > /dev/null

echo -e "Finsihed deploying.\n"
