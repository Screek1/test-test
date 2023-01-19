#!/bin/bash
./bin/console app:configure-bus-stops-search
./bin/console app:parse-bus-stops-data dataFiles/busStops/Bus_stops_Alberta.csv
./bin/console app:parse-bus-stops-data dataFiles/busStops/Bus_stops_Atlantic.csv
./bin/console app:parse-bus-stops-data dataFiles/busStops/Bus_stops_British_Columbia.csv
./bin/console app:parse-bus-stops-data dataFiles/busStops/Bus_stops_Ontario.csv
./bin/console app:parse-bus-stops-data dataFiles/busStops/Bus_Stops_Saskatchewan.csv