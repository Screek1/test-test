#!/bin/bash
./bin/console app:configure-crime-search
./bin/console app:parse-demography-data crime dataFiles/crime all_canada_crime_2022_ver3 -vv
