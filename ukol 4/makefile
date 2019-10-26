FILES = evacuation parking
make:
	$(foreach file,$(FILES), rm -fv $(file); echo "php ./src/$(file).php" > ./$(file); chmod +x ./$(file);)

	echo "python ./src/groups.py" > ./groups; chmod +x ./groups