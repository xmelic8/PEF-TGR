# Predmet: TGR
# Ukol: 4
# Cast: 2 (groups)
# Datum: 2019
# Autor: Michal Melichar
# email: xmelich8@mendelu.cz

import sys
import numpy

class Parser:
	def __init__(self):
		self.listPersons = []
		self.matrixRelationShips = []

	def runParser(self):
		inputSource = sys.stdin

		self.listPersons = inputSource.readline().strip().split(", ")

		self.initMatrix()

		for binding in inputSource:
			bindingData = binding.strip().split(" - ")

			firstPerson = self.listPersons.index(bindingData[0])
			secondPerson = self.listPersons.index(bindingData[1])

			if (firstPerson is None) or (secondPerson is None) or self.checkListPerson():
				continue

			self.matrixRelationShips[firstPerson, secondPerson] = 1
			self.matrixRelationShips[secondPerson, firstPerson] = 1


	def initMatrix(self):
		sizeMatrix = len(self.listPersons)
		self.matrixRelationShips = numpy.matrix(numpy.zeros((sizeMatrix,sizeMatrix), dtype = numpy.int))

	def checkListPerson(self):
		for person in self.listPersons:
			if(person is None):
				return 1
		return 0

class Analyzer:
	def __init__(self, listPersons, matrixRelationShips):
		self.listPersons = listPersons
		self.matrixRelationShips = matrixRelationShips

	def runAnalyzation(self):
		self.modifyPersonArray()
		self.coloring()

	def modifyPersonArray(self):
		returnArray = []

		for person in self.listPersons:
			returnArray.append([person])

		self.listPersons = returnArray

	def findBinding(self):
		personsCount = len(self.listPersons)

		for i in range(personsCount):
			for j in range(personsCount):
				if (i != j) and (self.matrixRelationShips[i,j] == 0):
					return [i, j]

		return [None, None]

	def changeMatrix(self, item):
		firstNode = item[0]
		secondNode = item[1]
		firtsElement = 0
		secondElement = 1

		self.matrixRelationShips[firstNode] += self.matrixRelationShips[secondNode]
		self.matrixRelationShips = self.matrixRelationShips.transpose()
		self.matrixRelationShips[firstNode] += self.matrixRelationShips[secondNode]

		supplement = list(set(range(firtsElement, len(self.listPersons))) - set([secondNode]))

		self.matrixRelationShips = self.matrixRelationShips.take(supplement, firtsElement).take(supplement, secondElement)

		self.listPersons[firstNode].append(self.listPersons[secondNode][firtsElement])
		del self.listPersons[secondNode]

	def coloring(self):
		firtsElement = 0
		firtsArgument = len(self.matrixRelationShips.nonzero()[firtsElement].tolist()[firtsElement])
		seconfArgument = len(self.listPersons)**2 - len(self.listPersons)

		while firtsArgument != seconfArgument:
			self.changeMatrix(self.findBinding())
			firtsArgument = len(self.matrixRelationShips.nonzero()[firtsElement].tolist()[firtsElement])
			seconfArgument = len(self.listPersons)**2 - len(self.listPersons)

class Printer:
	def __init__(self, listPersons):
		self.listPersons = listPersons

	def runPrinter(self):
		for group in analyzer.listPersons:
			print ','.join(group)


parser = Parser()
parser.runParser()
analyzer = Analyzer(parser.listPersons, parser.matrixRelationShips)
analyzer.runAnalyzation()
printer = Printer(analyzer.listPersons)
printer.runPrinter()