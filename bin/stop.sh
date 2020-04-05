#!/bin/bash
# zatrzymanie serwera, tylko jeśli jest uruchomiony
$php bin/console server:status -q > /dev/null 2>&1 && $php bin/console server:stop
symfony local:server:stop
