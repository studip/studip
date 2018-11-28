<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
	<xsl:output method="text" encoding="UTF-8"/>
	<xsl:template match="/">
    <xsl:text>&#xFEFF;</xsl:text>
	<xsl:for-each select="studip">
	<xsl:for-each select="institut">
<xsl:choose>
	<xsl:when test="type"><xsl:value-of select="type"/></xsl:when>
	<xsl:otherwise>Einrichtung</xsl:otherwise>
</xsl:choose>: <xsl:value-of select="name"/>
<xsl:if test="fakultaet">
Fakultät: <xsl:value-of select="fakultaet"/>
</xsl:if>
<xsl:if test="homepage">
Homepage: <xsl:value-of select="homepage"/>
</xsl:if>
<xsl:if test="strasse">
Strasse: <xsl:value-of select="strasse"/>
</xsl:if>
<xsl:if test="plz">
Postleitzahl: <xsl:value-of select="plz"/>
</xsl:if>
<xsl:if test="telefon">
Telefon: <xsl:value-of select="telefon"/>
</xsl:if>
<xsl:if test="fax">
Fax: <xsl:value-of select="fax"/>
</xsl:if>
<xsl:if test="email">
E-mail: <xsl:value-of select="email"/>
</xsl:if>
<xsl:if test="datenfelder">
<xsl:for-each select="datenfelder/datenfeld"><xsl:text>
</xsl:text><xsl:value-of select="@key"/>: <xsl:value-of select="."/>
</xsl:for-each>
</xsl:if>
<xsl:text>
</xsl:text>
<xsl:if test="seminare">
Veranstaltungen
			<xsl:choose>
				<xsl:when test="seminare/gruppe">
				<xsl:for-each select="seminare/gruppe">
Gruppe: <xsl:value-of select="@key"/><xsl:text>
</xsl:text>
					<xsl:choose>
					<xsl:when test="untergruppe">
						<xsl:for-each select="untergruppe">
Untergruppe:<xsl:value-of select="@key"/><xsl:text>
</xsl:text>
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
		<xsl:if test="personen">
MitarbeiterInnen
						<xsl:choose>
							<xsl:when test="personen/gruppe">
								<xsl:for-each select="personen/gruppe">
<xsl:text>
</xsl:text><xsl:value-of select="@key"/><xsl:text>
</xsl:text>
								<xsl:call-template name="showperson"/>
								</xsl:for-each>
							</xsl:when>
							<xsl:otherwise>
								<xsl:for-each select="personen">
									<xsl:call-template name="showperson"/>
								</xsl:for-each>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:if>
				</xsl:for-each>

				Generiert von Stud.IP Version <xsl:value-of select="@version"/>
			</xsl:for-each>
	</xsl:template>

<xsl:template name="showperson">
	<xsl:for-each select="person">
<xsl:if test="titel">
				<xsl:value-of select="titel"/><xsl:text> </xsl:text>
				</xsl:if>
				<xsl:value-of select="vorname"/>
				<xsl:text> </xsl:text>
				<xsl:value-of select="nachname"/>
				<xsl:if test="titel2">
					<xsl:text>, </xsl:text><xsl:value-of select="titel2"/>
				</xsl:if>
				<xsl:if test="telefon">
Telefon: <xsl:value-of select="telefon"/>
				</xsl:if>
				<xsl:if test="raum">
Raum: <xsl:value-of select="raum"/>
				</xsl:if>
				<xsl:if test="sprechzeiten">
Sprechzeit: <xsl:value-of select="sprechzeiten"/>
				</xsl:if>
				<xsl:if test="email">
E-Mail: <xsl:value-of select="email"/>
				</xsl:if>
<xsl:text>
</xsl:text>
	</xsl:for-each>
</xsl:template>

<xsl:template name="showseminar">
	<xsl:for-each select="seminar">
Veranstaltung - <xsl:for-each select="dozenten/dozent">
			<xsl:if test="position() &gt; 1">
				<xsl:text>, </xsl:text>
			</xsl:if>
			<xsl:value-of select="."/>
		</xsl:for-each> - <xsl:value-of select="titel"/> -
		<xsl:if test="untertitel">
Untertitel: <xsl:value-of select="untertitel"/>
		</xsl:if>
Lehrende: <xsl:for-each select="dozenten/dozent">
					<xsl:if test="position() &gt; 1">
						<xsl:text>, </xsl:text>
					</xsl:if>
					<xsl:value-of select="."/>
				</xsl:for-each>
Termin: <xsl:value-of select="termine/termin"/>
Erster Termin: <xsl:value-of select="termine/erstertermin"/>
		<xsl:if test="termine/vorbesprechung">
Vorbesprechung: <xsl:value-of select="termine/vorbesprechung"/>
		</xsl:if>
		<xsl:if test="status">
Status: <xsl:value-of select="status"/>
		</xsl:if>
		<xsl:if test="beschreibung">
Beschreibung: <xsl:value-of select="beschreibung"/>
		</xsl:if>
		<xsl:if test="raum">
Raum: <xsl:value-of select="raum"/>
		</xsl:if>
		<xsl:if test="sonstiges">
Sonstiges: <xsl:value-of select="sonstiges"/>
		</xsl:if>
		<xsl:if test="art">
Art der Veranstaltung: <xsl:value-of select="art"/>
		</xsl:if>
		<xsl:if test="teilnehmer">
Teilnahme: <xsl:value-of select="teilnehmer"/>
		</xsl:if>
		<xsl:if test="teilnehmerzahl">	<xsl:for-each select="teilnehmerzahl"><xsl:text>
</xsl:text><xsl:value-of select="@key"/> Anzahl Teilnehmende: <xsl:value-of select="."/>
		</xsl:for-each></xsl:if>
		<xsl:if test="voraussetzung">
Voraussetzungen: <xsl:value-of select="voraussetzung"/>
		</xsl:if>
		<xsl:if test="lernorga">
Lernorganisation: <xsl:value-of select="lernorga"/>
		</xsl:if>
		<xsl:if test="schein">
Leistungsnachweis: <xsl:value-of select="schein"/>
		</xsl:if>
		<xsl:if test="ects">
ECTS: <xsl:value-of select="ects"/>
		</xsl:if>
		<xsl:if test="bereiche">
Bereich:
<xsl:for-each select="bereiche/bereich"><xsl:value-of select="."/><xsl:text>
</xsl:text></xsl:for-each>
		</xsl:if>
		<xsl:if test="lvgruppen">
Module: 
<xsl:for-each select="lvgruppen/lvgruppe"><xsl:value-of select="."/><xsl:text>
</xsl:text></xsl:for-each>
		</xsl:if>
		<xsl:if test="datenfelder">
		<xsl:for-each select="datenfelder/datenfeld">
<xsl:value-of select="@key"/>: <xsl:value-of select="."/>
		</xsl:for-each>
		</xsl:if>
<xsl:text>
</xsl:text>
	</xsl:for-each>
</xsl:template>
</xsl:stylesheet>
