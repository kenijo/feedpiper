::-----------------------------------------------------------------------------
:: @author       Kenrick JORUS
:: @copyright    2023 Kenrick JORUS
:: @license      MIT License
:: @link         http://kenijo.github.io/feedpiper/
:: @description  Initialization and update of submodules
::-----------------------------------------------------------------------------

@echo off

:: Clone the feedpiper repository then the submodules
::git clone https://github.com/kenijo/feedpiper
::git submodule update --init --recursive

:: Clone the feedpiper repository with the submodules all at once:
::git clone --recurse-submodules https://github.com/kenijo/feedpiper

:: Add a new submodule to the feedpiper repository
::git submodule add https://github.com/user/repo include/repo

:: Remove a submodule from th feedpiper repository
::# Remove the submodule entry from .git/config
::git submodule deinit -f include/repo
::# Remove the submodule directory from the superproject's .git/modules directory
::rm -rf .git/modules/library/repo
::# Remove the entry in .gitmodules and remove the submodule directory located at path/to/submodule
::git rm -f include/repo

:: Current list of submodules in the feedpiper repository (defined in .gitmodules)
::git clone https://github.com/erusev/parsedown
::git clone https://github.com/simplehtmldom/simplehtmldom
::git clone https://github.com/simplepie/simplepie

:: Initialize cloning of all submodules
git submodule update --init --recursive

:: Update (pull=fetch+merge) of all submodules
git submodule foreach git pull origin master
