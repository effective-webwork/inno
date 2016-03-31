
## mit diesem Skript wird geprüft, ob genug Teammitglieder (6) an der Befragung teilgenommen haben.
# wenn ja, wird eine Auswertung für das entsprechende Team generiert
# wenn nein, wird keine Teamauswertung vorgenommen, die Angaben der Personen fließen in die Auswertung der Befragung auf Organisationsebene ein.
# entsprechende Hinweistexte werden in den html-Ergebnisbericht geschrieben.


daFunSkala <- function(items){
	dat <- datenMA[,items]
	if(nrow(dat[complete.cases(dat),]) > THRESHOLD){
	dat <- cbind(dat,id=datenMA$id,fkFi=datenMA$fkFi,fkVer=datenMA$fkVer,teamWahl=datenMA$teamWahl)
	dat.clean <- dat[complete.cases(dat),]
	dat.clean$skala <- rowMeans(dat[complete.cases(dat),1:length(items)],na.rm=T)
	return(dat.clean)} 	else {cat("NICHT GENUG PERSONEN.")} 
}
## zu kleine Frames geben NULL aus. GUT!

daFunOrg <- function(dat){
	if(is.data.frame(dat)){
		tempFrame <- data.frame(Team="Gesamte Organisation", Mittelwert=NA, Standardabweichung=NA, Teamgroesse=NA, Bewertung=NA, stringsAsFactors=FALSE)	
		tempFrame[1,"Mittelwert"] <- mean(dat$skala,na.rm=T)
		tempFrame[1,"Standardabweichung"] <- sd(dat$skala,na.rm=T)
		tempFrame[1,"Teamgroesse"] <- nrow(dat)
		return(tempFrame)
	} else {
		print('Kein data.frame fuer Orgebene.')
	}
}

daFunTeamOLD <- function(dat){
	if(is.data.frame(dat)){
		dat <- dat[-which(dat$teamWahl %in% c("kT","kgT")),] # nach dem entfernen N checken 
		tempNumTeams <- length(levels(factor(dat$teamWahl)))
		teamFrame <- data.frame(Team=levels(factor(dat$teamWahl)), Mittelwert=rep(NA,tempNumTeams), Standardabweichung=rep(NA,tempNumTeams), Teamgroesse=rep(NA,tempNumTeams), Bewertung=rep(NA,tempNumTeams), stringsAsFactors=FALSE)
		dat$teamWahl <- factor(dat$teamWahl)
		teamFrame$Mittelwert <- as.numeric(tapply(dat$skala,dat$teamWahl,mean,na.rm=T))
		teamFrame$Standardabweichung <- as.numeric(tapply(dat$skala,dat$teamWahl,sd,na.rm=T))
		teamFrame$Teamgroesse <- as.integer(table(dat$teamWahl))
		teamFrame <- teamFrame[which(teamFrame$Teamgroesse > THRESHOLD),]
		if(length(levels(factor(dat$teamWahl))) > length(levels(factor(teamFrame$Team)))){
			print('Teams gefiltert.')
			Out('</br></br>Hinweis: Es wurden fuer diesen Aspekt Teams aus Datenschutzgruenden entfernt.')
	}
		return(teamFrame)
	} else {
		print('Kein data.frame fuer Teamebene.')
	}	
}

daFunTeam <- function(dat){
	if(is.data.frame(dat)){
		dat <- dat[-which(dat$teamWahl %in% c("kT","kgT")),]
		if(any(table(dat$teamWahl) > THRESHOLD)){
			tempNumTeams <- length(levels(factor(dat$teamWahl)))
			teamFrame <- data.frame(Team=levels(factor(dat$teamWahl)), Mittelwert=rep(NA,tempNumTeams), Standardabweichung=rep(NA,tempNumTeams), Teamgroesse=rep(NA,tempNumTeams), Bewertung=rep(NA,tempNumTeams), stringsAsFactors=FALSE)
			dat$teamWahl <- factor(dat$teamWahl)
			teamFrame$Mittelwert <- as.numeric(tapply(dat$skala,dat$teamWahl,mean,na.rm=T))
			teamFrame$Standardabweichung <- as.numeric(tapply(dat$skala,dat$teamWahl,sd,na.rm=T))
			teamFrame$Teamgroesse <- as.integer(table(dat$teamWahl))
			teamFrame <- teamFrame[which(teamFrame$Teamgroesse > THRESHOLD),]
			if(length(levels(factor(dat$teamWahl))) > length(levels(factor(teamFrame$Team)))){
				print('Teams gefiltert.')
				Out('</br></br>Hinweis: Es wurden fuer diesen Aspekt Teams aus Datenschutzgruenden entfernt.')	
			}
		print('Teamframe ausgegeben.')
		return(teamFrame)
		} else {
			print('Kein Team ueber dem Schwellwert.')
			#Out('</br></br>Hinweis: Kein Team hat mit mehr als 5 Personen an der Befragung teilgenommen.')
		}
	} else {
		print('Kein data.frame fuer Teamebene.')
	}	
}


## zu kleine Frames oder Gruppen ausschliessen -- erledigt



daHtmlOut <- function(dat1,dat2){
	if(is.data.frame(dat1)){
		if(is.data.frame(dat2)){
			temp <- rbind(dat1,dat2)
			Out('</br></br>')
			Out(print.xtable(xtable(temp),type="html"))
			print('Beide df per HTML ausgegeben.')
		} else {
			temp <- dat1
			Out('</br></br>')
			Out(print.xtable(xtable(temp),type="html"))
			print('Org-df per HTML ausgegeben.')
			Out('</br></br>Hinweis: Es haben fuer diesen Aspekt nicht genug Personen fuer eine Teamauswertung teilgenommen.')
		}
	} else {
		print('KEIN df per HTML ausgegeben.')
		Out('</br></br>')
		Out('<b>Hinweis: Es haben fuer diesen Aspekt nicht genug Personen Auswertung auf Organisations- oder Teamebene teilgenommen.</b>')
	}
}

metaFun <- function(Items){
	if(exists('Items')){
		df <- daFunSkala(Items)
		dfOrg <- daFunOrg(df)
		if(is.data.frame(dfOrg)) {dfOrg <- daFunNorm(dfOrg,deparse(substitute(Items)))}
		dfTeam <- daFunTeam(df)
		if(is.data.frame(dfTeam)) {dfTeam <- daFunNorm(dfTeam,deparse(substitute(Items)))}
		daHtmlOut(dfOrg,dfTeam)
	} else {
		print('Fehler.')	
	}
}

daFunNorm <- function(df,it){
	vec <- numeric(nrow(df))
	for(i in 1:nrow(df)){
		vec[i] <- daFunNormComp(df[i,"Mittelwert"],normenMA[normenMA$items==it,"M.weight"],normenMA[normenMA$items==it,"SD.weight"])
	} 
	df$Bewertung <- vec
	return(df)
}

daFunNormComp <- function(m1,m2,s2){
	if(is.numeric(m1)){
		if(m1 < (m2-s2)) {
		return ("--") 
		} else if (m1 < (m2-(s2/2))) {
		return ("-") 
		} else if (m1 < (m2+(s2/2))) {
		return ("o")
		} else if (m1 < (m2+s2)) {
		return ("+")
		} else if (m1 >= (m2+s2)) {
		return ("++")
		} 			
	} else {return("kein Wert")}
}


