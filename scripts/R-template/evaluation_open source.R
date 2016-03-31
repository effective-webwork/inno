# Daten aufteilen

datenMA <- datenTool

## Bereinigung Mitarbeiter-Daten
datenMA <- datenMA[which(datenMA$grpFi!=""),]
datenMA <- datenMA[which(datenMA$grpFi=="grp2" & !is.na(datenMA$fi.fi6.)),]
rownames(datenMA) <- NULL

# Antworten vom Initiator der Befragung im Unternehmen - Daten sammeln
datenPLtemp <- data.frame(datenTool$wissMan.wiss1.,datenTool$wissMan.wiss2.,datenTool$wissMan.wiss3.,datenTool$wissMan.wiss4.,datenTool$wissMan.wiss5.,datenTool$branche,datenTool$mitArb,datenTool$kennZ)

## Bereinigung PL-Daten
datenPL <- datenPLtemp[complete.cases(datenPLtemp[,c(1:5,8)]),]

##########################################################################################
##Datenauswertung, hier auf die Dateinamen ahcten, ggf. anpassen.

if (nrow(datenMA)==0) {
	Out("<h1>Hinweis: Es haben keine Mitarbeiter den Fragebogen ausgefuellt.</h1>")
	print("Keine vollstaendigen MA-Daten")
	} else if (nrow(datenMA > 0)) {
	print("MA-Daten vorhanden. Starte Auswertung...")
	#Out('<h1>Jede Menge Text und Tabelle...</h1>')
	source("analysis-all-linear.R")
	#source("output-scales.R")
	source("output-text.R")
	}


