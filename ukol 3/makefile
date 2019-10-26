FILES = car race server
make:
	$(foreach file,$(FILES), rm -fv $(file); echo "php ./src/$(file).php" > ./$(file); chmod +x ./$(file);)