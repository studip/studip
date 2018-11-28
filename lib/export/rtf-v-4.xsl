<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:str="http://exslt.org/strings">
	<xsl:output method="text" encoding="UTF-8"/>
	<xsl:template match="/">
		<xsl:for-each select="studip">
			<xsl:text>{\rtf1\ansi\ansicpg1252\deff0\deflang1031{\fonttbl{\f0\fnil\fcharset0 Times New Roman;}}
\viewkind4\uc1\pard\par\qc\fs56 </xsl:text><xsl:value-of select="@range"/>
<xsl:text>\par\fs36 </xsl:text><xsl:value-of select="@uni"/>
<xsl:text>\par\pard\fs44 
\par 
\par 
\par 
\par 
\par 
\par 
\par 
\par 
\par 
\par 
\par 
\par 
\par 
\par 
\par 
\par 
\par
\par\qc\fs96 </xsl:text>
			<xsl:if test="institut/seminare">
				<xsl:text>Vorlesungskommentar</xsl:text>
			</xsl:if>
<xsl:text>\par\fs56 </xsl:text><xsl:value-of select="@zeitraum"/>
\par
\par\qr\fs24 Generiert von Stud.IP Version <xsl:value-of select="@version"/>
<xsl:text>\fs24\par\pard\page</xsl:text>
		<xsl:for-each select="institut">
			<xsl:text>
\fs36 </xsl:text><xsl:choose>
	<xsl:when test="type"><xsl:value-of select="type"/></xsl:when>
	<xsl:otherwise>Einrichtung</xsl:otherwise>
</xsl:choose>: <xsl:value-of select="name"/>
<xsl:if test="fakultaet">
			<xsl:text>
\par\par\fs24\b Fakultät: \b0 </xsl:text>
			<xsl:value-of select="fakultaet"/>
</xsl:if>
<xsl:if test="homepage">
			<xsl:text>
\par\b Homepage: \b0 </xsl:text>
			<xsl:value-of select="homepage"/>
</xsl:if>
<xsl:if test="strasse">
			<xsl:text>
\par\b Strasse: \b0 </xsl:text>
			<xsl:value-of select="strasse"/>
</xsl:if>
<xsl:if test="plz">
			<xsl:text>
\par\b Postleitzahl: \b0 </xsl:text>
			<xsl:value-of select="plz"/>
</xsl:if>
<xsl:if test="telefon">
			<xsl:text>
\par\b Telefon: \b0 </xsl:text>
			<xsl:value-of select="telefon"/>
</xsl:if>
<xsl:if test="fax">
			<xsl:text>
\par\b Fax: \b0 </xsl:text>
			<xsl:value-of select="fax"/>
</xsl:if>
<xsl:if test="email">
			<xsl:text>
\par\b E-mail: \b0 </xsl:text>
			<xsl:value-of select="email"/>
</xsl:if>
<xsl:if test="datenfelder">
	<xsl:for-each select="datenfelder/datenfeld">
			<xsl:text>
\par\b </xsl:text><xsl:value-of select="@key"/><xsl:text>: \b0 </xsl:text>
			<xsl:value-of select="."/>
	</xsl:for-each>
</xsl:if>
			<xsl:if test="seminare">
				<xsl:text>
\page\fs36 Veranstaltungen\fs24\par</xsl:text>
				<xsl:choose>
					<xsl:when test="seminare/gruppe">
						<xsl:for-each select="seminare/gruppe">
							<xsl:text>
\par\fs32\b </xsl:text>
							<xsl:value-of select="@key"/><xsl:text>\b0\fs24 </xsl:text>

							<xsl:choose>
								<xsl:when test="untergruppe">
									<xsl:for-each select="untergruppe">
										<xsl:text>
\par\fs28\b </xsl:text>
										<xsl:value-of select="@key"/><xsl:text>\b0\fs24 </xsl:text>
										<xsl:call-template name="showseminar"/>
									</xsl:for-each>
								</xsl:when>
								<xsl:otherwise>
									<xsl:call-template name="showseminar"/>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:for-each>
					</xsl:when>
					<xsl:otherwise>
						<xsl:for-each select="seminare">
							<xsl:call-template name="showseminar"/>
						</xsl:for-each>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:if>
		<xsl:text>
\page</xsl:text>
		</xsl:for-each>
		</xsl:for-each>
		<xsl:text> }</xsl:text>
	</xsl:template>


	<xsl:template name="showseminar">
		<xsl:for-each select="seminar">
			<xsl:text>
\par\brdrt\brdrs\brdrw10\brsp20 \brdrl\brdrs\brdrw10\brsp80 \brdrb
\brdrs\brdrw10\brsp20 \brdrr\brdrs\brdrw10\brsp80 \adjustright \fs26\b\lang1031\cgrid { </xsl:text>
			<xsl:for-each select="dozenten/dozent">
				<xsl:if test="position() &gt; 1">
					<xsl:text>, </xsl:text>
				</xsl:if>
				<xsl:value-of select="."/>
			</xsl:for-each> - <xsl:value-of select="titel"/>
			<xsl:text>\b0\par }\pard</xsl:text>
			<xsl:if test="untertitel">
				<xsl:text>
\b Untertitel: \b0 </xsl:text>
				<xsl:value-of select="untertitel"/>
				<xsl:text>\par </xsl:text>
			</xsl:if>
			<xsl:text>
\b Lehrende: \b0 </xsl:text><xsl:for-each select="dozenten/dozent">
				<xsl:if test="position() &gt; 1">
					<xsl:text>, </xsl:text>
				</xsl:if>
				<xsl:value-of select="."/>
			</xsl:for-each>
			<xsl:text>
\par\b Termin: \b0 </xsl:text><xsl:value-of select="termine/termin"/>
			<xsl:text>
\par\b Erster Termin: \b0 </xsl:text><xsl:value-of select="termine/erstertermin"/>
			<xsl:if test="termine/vorbesprechung">
				<xsl:text>
\par\b Vorbesprechung: \b0 </xsl:text><xsl:value-of select="termine/vorbesprechung"/>
			</xsl:if>
			<xsl:if test="status">
				<xsl:text>
\par\b Status: \b0 </xsl:text><xsl:value-of select="status"/>
			</xsl:if>
			<xsl:if test="veranstaltungsnummer">
				<xsl:text>
\par\b Veranstaltungsnummer: \b0 </xsl:text><xsl:value-of select="veranstaltungsnummer"/>
			</xsl:if>
			<xsl:if test="beschreibung">
				<xsl:text>
\par\b Beschreibung: \b0 </xsl:text><xsl:value-of select="str:replace(beschreibung, '&#10;', '\line ')"/>
			</xsl:if>
			<xsl:if test="raum">
				<xsl:text>
\par\b Raum: \b0 </xsl:text><xsl:value-of select="raum"/>
			</xsl:if>
			<xsl:if test="sonstiges">
				<xsl:text>
\par\b Sonstiges: \b0 </xsl:text><xsl:value-of select="sonstiges"/>
			</xsl:if>
			<xsl:if test="art">
				<xsl:text>
\par\b Art der Veranstaltung: \b0 </xsl:text><xsl:value-of select="art"/>
			</xsl:if>
			<xsl:if test="teilnehmer">
				<xsl:text>
\par\b Teilnahme: \b0 </xsl:text><xsl:value-of select="teilnehmer"/>
			</xsl:if>
			<xsl:if test="teilnehmerzahl">
				<xsl:for-each select="teilnehmerzahl">
				<xsl:text>
\par\b </xsl:text><xsl:value-of select="@key"/><xsl:text> Anzahl Teilnehmende: \b0 </xsl:text><xsl:value-of select="."/>
				</xsl:for-each>
			</xsl:if>
			<xsl:if test="voraussetzung">
				<xsl:text>
\par\b Voraussetzungen: \b0 </xsl:text><xsl:value-of select="voraussetzung"/>
			</xsl:if>
			<xsl:if test="lernorga">
				<xsl:text>
\par\b Lernorganisation: \b0 </xsl:text><xsl:value-of select="lernorga"/>
			</xsl:if>
			<xsl:if test="schein">
				<xsl:text>
\par\b Leistungsnachweis: \b0 </xsl:text><xsl:value-of select="schein"/>
			</xsl:if>
			<xsl:if test="ects">
				<xsl:text>
\par\b ECTS: \b0 </xsl:text><xsl:value-of select="ects"/>
			</xsl:if>
			<xsl:if test="bereiche">
				<xsl:text>
\par\b Bereich: \b0 </xsl:text>
				<xsl:for-each select="bereiche/bereich">
\par <xsl:value-of select="."/>
				</xsl:for-each>
			</xsl:if>
			<xsl:if test="lvgruppen">
				<xsl:text>
\par\b Module: \b0 </xsl:text>
				<xsl:for-each select="lvgruppen/lvgruppe">
\par <xsl:value-of select="."/>
				</xsl:for-each>
			</xsl:if>
			<xsl:if test="datenfelder">
				<xsl:for-each select="datenfelder/datenfeld">
					<xsl:text>
\par\b </xsl:text><xsl:value-of select="@key"/><xsl:text>: \b0 </xsl:text>
					<xsl:value-of select="."/>
				</xsl:for-each>
			</xsl:if>
			<xsl:text>
\par </xsl:text>
		</xsl:for-each>
	</xsl:template>
</xsl:stylesheet>
