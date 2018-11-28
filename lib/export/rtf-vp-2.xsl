<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
	<xsl:output method="text" encoding="UTF-8"/>
	<xsl:template match="/">
			<xsl:text>{\rtf1\ansi\ansicpg1252\deff0\deflang1031{\fonttbl{\f0\fnil\fcharset0 Times New Roman;}}
\viewkind4\uc1\pard\par\</xsl:text>

		<xsl:for-each select="studip">
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
			<xsl:text>
\par\trowd \trgaph70\trleft-70\trbrdrt\brdrs\brdrw10 \trbrdrl\brdrs\brdrw10 \trbrdrb\brdrs\brdrw10 
\trbrdrr\brdrs\brdrw10 \trbrdrh\brdrs\brdrw10 \trbrdrv\brdrs\brdrw10 \clvertalt\clbrdrt\brdrs\brdrw10 \clbrdrl\brdrs\brdrw10 \clbrdrb\brdrs\brdrw10 \clbrdrr\brdrs\brdrw10 \cltxlrtb \cellx1839\clvertalt\clbrdrt\brdrs\brdrw10 \clbrdrl\brdrs\brdrw10 \clbrdrb
\brdrs\brdrw10 \clbrdrr\brdrs\brdrw10 \cltxlrtb \cellx3748\clvertalt\clbrdrt\brdrs\brdrw10 \clbrdrl\brdrs\brdrw10 \clbrdrb\brdrs\brdrw10 \clbrdrr\brdrs\brdrw10 \cltxlrtb \cellx5657\clvertalt\clbrdrt\brdrs\brdrw10 \clbrdrl\brdrs\brdrw10 \clbrdrb
\brdrs\brdrw10 \clbrdrr\brdrs\brdrw10 \cltxlrtb \cellx7566\clvertalt\clbrdrt\brdrs\brdrw10 \clbrdrl\brdrs\brdrw10 \clbrdrb\brdrs\brdrw10 \clbrdrr\brdrs\brdrw10 \cltxlrtb \cellx9475\pard\plain \nowidctlpar\intbl\adjustright \lang1031\cgrid 
{\fs24\b Lehrende\b0\cell\b Veranstaltung\b0\cell\b Status\b0\cell\b Termin\b0\cell\b Raum\b0\cell }\pard \nowidctlpar\widctlpar\intbl\adjustright {\row }\pard</xsl:text>
				<xsl:choose>
					<xsl:when test="seminare/gruppe">
						<xsl:for-each select="seminare/gruppe">
			<xsl:text>
\brdrt\brdrs\brdrw10\brsp20 \brdrl\brdrs\brdrw10\brsp80 \brdrb
\brdrs\brdrw10\brsp20 \brdrr\brdrs\brdrw10\brsp80 \adjustright \fs28\lang1031\cgrid { </xsl:text>
							<xsl:value-of select="@key"/>
			<xsl:text>\par }\pard</xsl:text>
							<xsl:choose>
								<xsl:when test="untergruppe">
									<xsl:for-each select="untergruppe">
			<xsl:text>
\brdrt\brdrs\brdrw10\brsp20 \brdrl\brdrs\brdrw10\brsp80 \brdrb
\brdrs\brdrw10\brsp20 \brdrr\brdrs\brdrw10\brsp80 \adjustright \fs24\lang1031\cgrid { </xsl:text>
							<xsl:value-of select="@key"/>
			<xsl:text>\par }\pard</xsl:text>
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
				<xsl:text>
\page\par\fs36 MitarbeiterInnen\fs24\par </xsl:text>
			<xsl:text>
\par\trowd \trgaph70\trleft-70\trbrdrt\brdrs\brdrw10 \trbrdrl\brdrs\brdrw10 \trbrdrb\brdrs\brdrw10 
\trbrdrr\brdrs\brdrw10 \trbrdrh\brdrs\brdrw10 \trbrdrv\brdrs\brdrw10 \clvertalt\clbrdrt\brdrs\brdrw10 \clbrdrl\brdrs\brdrw10 \clbrdrb\brdrs\brdrw10 \clbrdrr\brdrs\brdrw10 \cltxlrtb \cellx1839\clvertalt\clbrdrt\brdrs\brdrw10 \clbrdrl\brdrs\brdrw10 \clbrdrb
\brdrs\brdrw10 \clbrdrr\brdrs\brdrw10 \cltxlrtb \cellx3748\clvertalt\clbrdrt\brdrs\brdrw10 \clbrdrl\brdrs\brdrw10 \clbrdrb\brdrs\brdrw10 \clbrdrr\brdrs\brdrw10 \cltxlrtb \cellx5657\clvertalt\clbrdrt\brdrs\brdrw10 \clbrdrl\brdrs\brdrw10 \clbrdrb
\brdrs\brdrw10 \clbrdrr\brdrs\brdrw10 \cltxlrtb \cellx7566\clvertalt\clbrdrt\brdrs\brdrw10 \clbrdrl\brdrs\brdrw10 \clbrdrb\brdrs\brdrw10 \clbrdrr\brdrs\brdrw10 \cltxlrtb \cellx9475\pard\plain \nowidctlpar\intbl\adjustright \lang1031\cgrid 
{\fs24\b Name\b0\cell\b Telefon\b0\cell\b Raum\b0\cell\b Sprechzeit\b0\cell\b E-Mail\b0\cell }\pard \nowidctlpar\widctlpar\intbl\adjustright {\row }\pard</xsl:text>
				<xsl:choose>
					<xsl:when test="personen/gruppe">
						<xsl:for-each select="personen/gruppe">
			<xsl:text>
\brdrt\brdrs\brdrw10\brsp20 \brdrl\brdrs\brdrw10\brsp80 \brdrb
\brdrs\brdrw10\brsp20 \brdrr\brdrs\brdrw10\brsp80 \adjustright \fs24\lang1031\cgrid { </xsl:text>
							<xsl:value-of select="@key"/>
			<xsl:text>\par }\pard</xsl:text>
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
		<xsl:text>
\page</xsl:text>
		</xsl:for-each>
\par\qr\fs24 Generiert von Stud.IP Version <xsl:value-of select="@version"/>
		</xsl:for-each>
		<xsl:text> }</xsl:text>
	</xsl:template>

	<xsl:template name="showperson">
		<xsl:for-each select="person">
			<xsl:text>
\trowd \trgaph70\trleft-70\trbrdrt\brdrs\brdrw10 \trbrdrl\brdrs\brdrw10 \trbrdrb\brdrs\brdrw10 
\trbrdrr\brdrs\brdrw10 \trbrdrh\brdrs\brdrw10 \trbrdrv\brdrs\brdrw10 \clvertalt\clbrdrt\brdrs\brdrw10 \clbrdrl\brdrs\brdrw10 \clbrdrb\brdrs\brdrw10 \clbrdrr\brdrs\brdrw10 \cltxlrtb \cellx1839\clvertalt\clbrdrt\brdrs\brdrw10 \clbrdrl\brdrs\brdrw10 \clbrdrb
\brdrs\brdrw10 \clbrdrr\brdrs\brdrw10 \cltxlrtb \cellx3748\clvertalt\clbrdrt\brdrs\brdrw10 \clbrdrl\brdrs\brdrw10 \clbrdrb\brdrs\brdrw10 \clbrdrr\brdrs\brdrw10 \cltxlrtb \cellx5657\clvertalt\clbrdrt\brdrs\brdrw10 \clbrdrl\brdrs\brdrw10 \clbrdrb
\brdrs\brdrw10 \clbrdrr\brdrs\brdrw10 \cltxlrtb \cellx7566\clvertalt\clbrdrt\brdrs\brdrw10 \clbrdrl\brdrs\brdrw10 \clbrdrb\brdrs\brdrw10 \clbrdrr\brdrs\brdrw10 \cltxlrtb \cellx9475\pard\plain \nowidctlpar\intbl\adjustright \lang1031\cgrid 
{\fs24 </xsl:text>
			<xsl:if test="titel">
				<xsl:value-of select="titel"/><xsl:text> </xsl:text>
			</xsl:if>
				<xsl:value-of select="vorname"/>
				<xsl:text> </xsl:text>
				<xsl:value-of select="nachname"/>
			<xsl:if test="titel2">
				<xsl:text>, </xsl:text><xsl:value-of select="titel2"/>
			</xsl:if>
<xsl:text>\cell </xsl:text>
			<xsl:if test="telefon">
				<xsl:value-of select="telefon"/>
			</xsl:if>
<xsl:text>\cell </xsl:text>
			<xsl:if test="raum">
				<xsl:value-of select="raum"/>
			</xsl:if>
<xsl:text>\cell </xsl:text>
			<xsl:if test="sprechzeiten">
				<xsl:value-of select="sprechzeiten"/>
			</xsl:if>
<xsl:text>\cell </xsl:text>
			<xsl:if test="email">
				<xsl:value-of select="email"/>
			</xsl:if>
<xsl:text>\cell }\pard \nowidctlpar\widctlpar\intbl\adjustright {\row }\pard</xsl:text>
		</xsl:for-each>
	</xsl:template>

	<xsl:template name="showseminar">
		<xsl:for-each select="seminar">
			<xsl:text>
\trowd \trgaph70\trleft-70\trbrdrt\brdrs\brdrw10 \trbrdrl\brdrs\brdrw10 \trbrdrb\brdrs\brdrw10 
\trbrdrr\brdrs\brdrw10 \trbrdrh\brdrs\brdrw10 \trbrdrv\brdrs\brdrw10 \clvertalt\clbrdrt\brdrs\brdrw10 \clbrdrl\brdrs\brdrw10 \clbrdrb\brdrs\brdrw10 \clbrdrr\brdrs\brdrw10 \cltxlrtb \cellx1839\clvertalt\clbrdrt\brdrs\brdrw10 \clbrdrl\brdrs\brdrw10 \clbrdrb
\brdrs\brdrw10 \clbrdrr\brdrs\brdrw10 \cltxlrtb \cellx3748\clvertalt\clbrdrt\brdrs\brdrw10 \clbrdrl\brdrs\brdrw10 \clbrdrb\brdrs\brdrw10 \clbrdrr\brdrs\brdrw10 \cltxlrtb \cellx5657\clvertalt\clbrdrt\brdrs\brdrw10 \clbrdrl\brdrs\brdrw10 \clbrdrb
\brdrs\brdrw10 \clbrdrr\brdrs\brdrw10 \cltxlrtb \cellx7566\clvertalt\clbrdrt\brdrs\brdrw10 \clbrdrl\brdrs\brdrw10 \clbrdrb\brdrs\brdrw10 \clbrdrr\brdrs\brdrw10 \cltxlrtb \cellx9475\pard\plain \nowidctlpar\intbl\adjustright \lang1031\cgrid 
{\fs24 </xsl:text>
			<xsl:value-of select="titel"/>
<xsl:text>\cell </xsl:text>
			<xsl:for-each select="dozenten/dozent">
				<xsl:if test="position() &gt; 1">
					<xsl:text>, </xsl:text>
				</xsl:if>
				<xsl:value-of select="."/>
			</xsl:for-each>
<xsl:text>\cell </xsl:text>
			<xsl:value-of select="status"/>
<xsl:text>\cell </xsl:text>
			<xsl:value-of select="termine/termin"/>
<xsl:text>\cell </xsl:text>
			<xsl:value-of select="raum"/>
<xsl:text>\cell }\pard \nowidctlpar\widctlpar\intbl\adjustright {\row }\pard</xsl:text>
		</xsl:for-each>
	</xsl:template>
</xsl:stylesheet>