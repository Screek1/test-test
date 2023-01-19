#!/bin/bash
./bin/console app:configure-demography-search
./bin/console app:parse-demography-data demography dataFiles/demography map_canada_demo_2022_done

