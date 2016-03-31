# Vorbereitung des html-Ergebnisberichts
#
# 1a) Funktionen - siehe functions-open source.R
# 1b) Farben
# 2) HTML vorbereiten
# 2) Einlesen der Rohdaten
# 3) Datenaufbereitung - Basic

## Funktionen & Pakete

## Pakete
library(car) # fuer recode() u.a. in Datenauswertung der Teams
library(gplots) # Balkendiagramme
library(plotrix) # Spinnendiagramme
library(xtable) # fuer (HTML)Tabellen
library(R2HTML) # Ausgabe in HTML

## Plot in HTML einfuegen
insertPlot <- function( fileName, plotFunction, width=480, height=480, ...)
{
  # create output
  png(filename=file.path("out/img", fileName), width=width, height=height)
  plotFunction()
  dev.off()
  
  # insert into html
  Out(c('<img src="', file.path("img", fileName), '" />'))
}

## Plot in HTML einfuegen Ver. 2
insertPlot2 <- function( fileName, plotFunction, width=480, height=480,  ...)
{
  # create output
  png(filename=file.path("out/img", fileName), width=width, height=height)
  plotFunction(...)
  dev.off()
  
  # insert into html
  Out(c('<img src="', file.path("img", fileName), '" />'))
}

## Text und Formatierung in HTML schreiben
Out <- function ( str, ... )
{
	cat(str, file=file.path("out", "results.html"), append=TRUE)
}

## Feste Dezimalstellen
specify_decimal <- function(x, k) format(round(x, k), nsmall=k)

# Abbildungen
## Balkendiagramme angepasst

balkenDiagr <- function(daten=NULL,name="default",weite=2560,hoehe=1600,schrift=64,stufen=5){
png(file = paste0("./Abb/Abb_",name,".png"), bg = "white",width=weite,height=hoehe,pointsize=schrift)
x <- barplot2(table(factor(daten, levels=1:stufen))/length(daten)*100, xlab="", ylab="Relative Häufigkeit in %", col=c(colorInno02,colorInno04,colorInno06,colorInno08,colorInnoFull), main="",ylim=c(0,100),beside=T,offset=0.1,plot.grid=T)
text(x,y=table(factor(daten, levels=1:stufen))/length(daten)*100+5,labels=specify_decimal(table(factor(daten, levels=1:stufen))/length(daten)*100,1))
axis(1,labels=F,tick=T,tcl=0,lwd=1)
dev.off()
}

balkenAbb <- function(daten,name="default",weite=2560,hoehe=1600,schrift=64,stufen=5,farben=rainbow(5)){
png(file = paste0("./Abb/Abb_",name,".png"), bg = "white",width=weite,height=hoehe,pointsize=schrift)
x <- barplot2(table(factor(daten, levels=1:stufen))/length(daten)*100, xlab="", ylab="Relative Häufigkeit in %", col=colorInno, main="", ylim=c(0,100), beside=T, offset=0.0, plot.grid=T)
text(x,y=table(factor(daten, levels=1:stufen))/length(daten)*100+5,labels=specify_decimal(table(factor(daten, levels=1:stufen))/length(daten)*100,1))
axis(1,labels=F,tick=T,tcl=0,lwd=1)
dev.off()
}

## Spinnendiagramm

###########################################################################################
#Farben definieren

colorInno <- as.character()
colorInno[1] <- rgb(255,213,107,maxColorValue=255)
colorInno[2] <- rgb(255,192,86,maxColorValue=255)
colorInno[3] <- rgb(254,175,65,maxColorValue=255)
colorInno[4] <- rgb(254,149,43,maxColorValue=255)
colorInno[5] <- rgb(253,128,22,maxColorValue=255)
colorInno[6] <- rgb(236,112,5,maxColorValue=255)

grayInno <- as.character()
grayInno[1] <- rgb(228,229,229,maxColorValue=255)
grayInno[2] <- rgb(202,203,204,maxColorValue=255)
grayInno[3] <- rgb(176,177,178,maxColorValue=255)
grayInno[4] <- rgb(149,151,153,maxColorValue=255)
grayInno[5] <- rgb(123,125,127,maxColorValue=255)
grayInno[6] <- rgb(97,100,102,maxColorValue=255)

sanfteColInno <- as.character()
sanfteColInno[1] <- rgb(100,149,199,maxColorValue=255)
sanfteColInno[2] <- rgb(204,100,95,maxColorValue=255)
sanfteColInno[3] <- rgb(196,197,113,maxColorValue=255)


################################################################################################################
#html vorbereiten
# UTF-8 - Benötigt für die korrekte Darstellung von Umlauten
Sys.setlocale(locale="de_DE.utf8")

# Initialisiere HTML
dir.create(path="out/img", showWarnings=FALSE, recursive=TRUE)
dir.create("./Abb")

htmlFileConnection <- file(file.path("out", "results.html"), encoding="UTF-8")

Out('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">')
Out('<html><head><title>Auswertung Ihrer Umfrage</title><meta http-equiv="content-type" content="text/html; charset=UTF-8"><link rel="stylesheet" type="text/css" href="style.css" /></head><body>')

Out('<div class="wrapper evaluation">')
Out('<h1>Ergebnisbericht</h1>')

## Dateien kopieren, ggf. eine neue style-Datei f�r den html-Ergebnisbericht, Normtabellen etc. hinterlegen

system("cp ./staticfiles/style.css ./out/")
system("cp ./staticfiles/ReferenzKreiseROR-FL.csv ./")
system("cp ./staticfiles/NormdatenTool-FL.csv ./")



## Dateien einlesen
normenMA <- read.csv2("innomon-norm-tabelle.csv")


################################################################################################################

THRESHOLD <- 5 #Grenze, ab der Auswertung erfolgt, hier ab >= 6 erfolgt Auswertung

# Alle Daten von CSV importieren
try(tempDataCode <- read.csv("answers-all-code-short.csv"))
try(tempDataName <- read.csv("answers-all-code-long.csv"))

if(exists("tempDataCode")){
	datenTool <- tempDataCode
	print("datenTool angelegt")
	if(exists("tempDataName")){
		datenTool$teamWahl <- tempDataName$teamWahl
		print("Teamnamen integriert")
		}
	} else {
	Out('<h1>Hinweis: Es liegen keine Daten vor.</h1>')
	print('Hinweis: Es liegen keine Daten vor (+HTML Hinweis).')
	}


if(exists("datenTool")){
	if(nrow(datenTool[which(datenTool$grpFi=="grp2"),]) > THRESHOLD){
		print('Starte Auswertung')
		source("evaluation.R")
	} else {
		Out('<h1>Hinweis: Es liegen nicht genug vollstaendige Mitarbeiterdaten zur Auswertung vor.</h1>')
		print('Stopp, weniger als 5 MA (+HTML-Hinweis).')
		}
}

if(exists("datenTool")){
	if(nrow(datenTool[which(datenTool$grpFi=="grp4"),]) > 0){
		print('Starte PL-Auswertung')
		#source("evaluation-PL.R")
	} else {
		Out('<h1>Hinweis: Es liegen keine Daten des Initiators der Befragung in der Organisation vor.</h1>')
		print('Stopp, kein PL (+HTML-Hinweis).')
		}
}