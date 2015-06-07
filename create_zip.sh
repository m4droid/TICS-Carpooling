#!/bin/bash

zip -r "tarea3 - grupo23.zip" \
	../tarea3grupo23/bower_components \
	../tarea3grupo23/frontend \
	../tarea3grupo23/index.html \
	../tarea3grupo23/backend \
	../tarea3grupo23/sql \
	../tarea3grupo23/README.md \
	-x "*.DS_Store" 
