## footer.R

# Footer und HTML Abschluss --> unbedingt anpassen!
Out('</div>')

Out('<div id="footer"><div class="wrapper">')
Out('<h2>Innografie wird gef&ouml;rdert von</h2>')
Out('<div id="sponsoring">')
Out('<a href="http://www.XXX.de" target="_blank" id="sponsor_01" title="XXX">&nbsp;</a>')
Out('<a href="http://www.YYY.de" id="sponsor_02" target="_blank" title="YYY">&nbsp;</a>')
Out('<a href="http://ZZZ.htm" id="sponsor_03" target="_blank" title="ZZZ">&nbsp;</a>')
Out('<a href="http://www.QQQ.de" id="sponsor_04" target="_blank" title="QQQ">&nbsp;</a>')
Out('<a href="http://PPP.de" id="sponsor_05" target="_blank" title="PPP">&nbsp;</a>')
Out('<div class="clear"></div>')

Out('</body></html>')
close(htmlFileConnection)