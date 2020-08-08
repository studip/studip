<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
<!-- vosshe@fh-trier.de -->
<xsl:output method="text" encoding="UTF-8"/>
    <xsl:template match="/">
    <xsl:text>&#xFEFF;</xsl:text>
	<xsl:text>Einrichtung-Typ;</xsl:text>
	<xsl:text>Einrichtung-Name;</xsl:text>
	<xsl:text>Einrichtung-Fakultaet;</xsl:text>
	<xsl:text>Typ;</xsl:text>
	<xsl:text>Lehrende;</xsl:text>
	<xsl:text>Titel;</xsl:text>
	<xsl:text>Untertitel;</xsl:text>
	<xsl:text>Raum;</xsl:text>
	<xsl:text>Art;</xsl:text>
	<xsl:text>max. Teilnehmende;</xsl:text>
	<xsl:text>ECTS;</xsl:text>
	<xsl:text>Vorbesprechung;</xsl:text>
	<xsl:text>erster Termin;</xsl:text>
	<xsl:text>Termin;</xsl:text>
	<xsl:text>Lernorganisation;</xsl:text>
	<xsl:text>sonstiges</xsl:text>
<xsl:text>
</xsl:text>

        <xsl:for-each select="studip">
	    <xsl:for-each select="institut">
    		<xsl:for-each select="seminare">
		    <xsl:for-each select="gruppe">
			<xsl:call-template name="showseminar" />
    		    </xsl:for-each>
    		</xsl:for-each>
	    </xsl:for-each>
	</xsl:for-each>

</xsl:template>

<xsl:template name="showseminar">
    <xsl:for-each select="seminar">
    	<xsl:text>"</xsl:text>

	<xsl:value-of select="../../../type" />
	<xsl:text>";"</xsl:text>

	<xsl:value-of select="../../../name" />
	<xsl:text>";"</xsl:text>

	<xsl:value-of select="../../../fakultaet" />
	<xsl:text>";"</xsl:text>

	<xsl:value-of select="../@key" />
	<xsl:text>";"</xsl:text>

	<xsl:for-each select="dozenten/dozent">
	    <xsl:if test="position() &gt; 1">
		<xsl:text>, </xsl:text>
	    </xsl:if>
	    <xsl:value-of select="."/>
	</xsl:for-each>
	<xsl:text>";"</xsl:text>

	<xsl:value-of select="titel"/>
	<xsl:text>";"</xsl:text>

	<xsl:if test="untertitel">
	    <xsl:value-of select="untertitel"/>
	</xsl:if>
	<xsl:text>";"</xsl:text>

	<xsl:if test="raum">
	    <xsl:value-of select="raum"/>
	</xsl:if>
	<xsl:text>";"</xsl:text>

	<xsl:if test="art">
	    <xsl:value-of select="art"/>
	</xsl:if>
	<xsl:text>";"</xsl:text>

	<xsl:if test="teilnehmerzahl">
	    <xsl:value-of select="normalize-space(teilnehmerzahl)"/>
	</xsl:if>
	<xsl:text>";"</xsl:text>

	<xsl:if test="ects">
	    <xsl:value-of select="ects"/>
	</xsl:if>
	<xsl:text>";"</xsl:text>

	<xsl:if test="termine/vorbesprechung">
	    <xsl:value-of select="termine/vorbesprechung"/>
	</xsl:if>
	<xsl:text>";"</xsl:text>

	<xsl:if test="termine/erstertermin">
	    <xsl:value-of select="termine/erstertermin"/>
	</xsl:if>
	<xsl:text>";"</xsl:text>

	<xsl:if test="termine/termin">
	    <xsl:value-of select="termine/termin"/>
	</xsl:if>
	<xsl:text>";"</xsl:text>

	<xsl:if test="lernorga">
	    <xsl:value-of select="lernorga"/>
	</xsl:if>
	<xsl:text>";"</xsl:text>

	<xsl:if test="sonstiges">
	    <xsl:value-of select="sonstiges"/>
	</xsl:if>
	<xsl:text>"</xsl:text>

	<xsl:text>
</xsl:text>
    </xsl:for-each>
</xsl:template>

</xsl:stylesheet>
