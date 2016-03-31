# Datenaufbereitung - ganze Org

library(car) # für recode() u.a. in Datenauswertung-Team
library(gplots) # Balkendiagramme
library(plotrix) # Spinnendiagramme
library(xtable) # fuer (HTML)Tabellen
library(R2HTML)

# Datensatz vorbereiten
## Recoding von Fragen
datenMA$teamWahl <- factor(datenMA$teamWahl)
datenMA$teamWahl <- recode(datenMA$teamWahl,"'keinem Team'='kT';'keinem genannten Team'='kgT'")

datenMA$parti.par39. <- recode(datenMA$parti.par39.,"1=5;2=4;4=2;5=1")


### Arbeitsbedingungen

partiItems <- grep(c("parti[.]"),names(datenMA))


