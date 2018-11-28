<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
	<xsl:output method="html" encoding="UTF-8"/>
	<xsl:template match="/">
	<html>
        <meta charset="utf-8"/>
		<body>
		<xsl:for-each select="studip">
			<xsl:for-each select="institut">
				<h1><xsl:choose>
	<xsl:when test="type"><xsl:value-of select="type"/></xsl:when>
	<xsl:otherwise>Einrichtung</xsl:otherwise>
</xsl:choose>: <xsl:value-of select="name"/>
				</h1>
<xsl:if test="fakultaet">
				<b>Fakultät: </b>
				<xsl:value-of select="fakultaet"/>
				<br/>
</xsl:if>
<xsl:if test="homepage">
				<b>Homepage: </b>
				<xsl:value-of select="homepage"/>
				<br/>
</xsl:if>
<xsl:if test="strasse">
				<b>Strasse: </b>
				<xsl:value-of select="strasse"/>
				<br/>
</xsl:if>
<xsl:if test="plz">
				<b>Postleitzahl: </b>
				<xsl:value-of select="plz"/>
				<br/>
</xsl:if>
<xsl:if test="telefon">
				<b>Telefon: </b>
				<xsl:value-of select="telefon"/>
				<br/>
</xsl:if>
<xsl:if test="fax">
				<b>Fax: </b>
				<xsl:value-of select="fax"/>
				<br/>
</xsl:if>
<xsl:if test="email">
				<b>E-mail: </b>
				<xsl:value-of select="email"/>
				<br/>
</xsl:if>
<xsl:if test="datenfelder">
	<xsl:for-each select="datenfelder/datenfeld">
				<b><xsl:value-of select="@key"/>: </b>
				<xsl:value-of select="."/>
				<br/>
	</xsl:for-each>
</xsl:if>
				<br/>
				<xsl:if test="seminare">
					<table width="100%" cellpadding="5" cellspacing="2">
						<tr>
							<td>
								<h2>Veranstaltungen</h2>
							</td>
						</tr>
						<tr>
							<td>
								<br/>
							</td>
						</tr>
						<tr>
							<td width="20%" bgcolor="#006699">
								<b>
									<font color="#FFFFFF">Lehrende</font>
									</b>
								</td>
								<td width="35%" bgcolor="#006699">
									<b>
										<font color="#FFFFFF">Veranstaltung</font>
									</b>
								</td>
								<td width="20%" bgcolor="#006699">
									<b>
										<font color="#FFFFFF">Status</font>
									</b>
								</td>
								<td width="15%" bgcolor="#006699">
									<b>
										<font color="#FFFFFF">Termin</font>
									</b>
								</td>
								<td width="10%" bgcolor="#006699">
									<b>
										<font color="#FFFFFF">Raum</font>
									</b>
								</td>
							</tr>
						<xsl:choose>
							<xsl:when test="seminare/gruppe">
								<xsl:for-each select="seminare/gruppe">
									<tr bgcolor="#0000BB">
										<td colspan="5">
											<h2>
												<font color="#FFFFFF">
													<b>
													<xsl:value-of select="@key"/>
													</b>
												</font>
											</h2>
										</td>
									</tr>
									<xsl:choose>
										<xsl:when test="untergruppe">
											<xsl:for-each select="untergruppe">
												<tr bgcolor="#6600BB">
													<td colspan="5">
														<h2>
															<font color="#FFFFFF">
																<b>
																<xsl:value-of select="@key"/>
																</b>
															</font>
														</h2>
													</td>
												</tr>
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
					</table>
				</xsl:if>
				<xsl:if test="personen">
					<table width="100%" cellpadding="5" cellspacing="2">
						<tr colspan="3">
							<td>
								<h2>MitarbeiterInnen</h2>
							</td>
						</tr>
						<tr>
							<td>
								<br/>
							</td>
						</tr>
						<tr>
							<td bgcolor="#006699">
								<b>
									<font color="#FFFFFF">Name</font>
									</b>
								</td>
								<td bgcolor="#006699">
									<b>
										<font color="#FFFFFF">Telefon</font>
									</b>
								</td>
								<td bgcolor="#006699">
									<b>
										<font color="#FFFFFF">E-Mail</font>
									</b>
								</td>
							</tr>
						<xsl:choose>
							<xsl:when test="personen/gruppe">
								<xsl:for-each select="personen/gruppe">
									<tr bgcolor="#006699">
										<td colspan="3">
											<font color="#FFFFFF">
												<b>
													<xsl:value-of select="@key"/>
												</b>
											</font>
										</td>
									</tr>
								<xsl:call-template name="showperson"/>
								</xsl:for-each>
							</xsl:when>
							<xsl:otherwise>
								<xsl:for-each select="personen">
									<xsl:call-template name="showperson"/>
								</xsl:for-each>
							</xsl:otherwise>
						</xsl:choose>
						</table>
					</xsl:if>
					<br/>
					<br/>
				</xsl:for-each>
				<font size="-1">Generiert von Stud.IP Version <xsl:value-of select="@version"/></font>
			</xsl:for-each>
			</body>
		</html>
	</xsl:template>

<xsl:template name="showperson">
	<xsl:for-each select="person">
		<tr>
			<td bgcolor="#EEEEEE">
				<xsl:if test="titel">
					<xsl:value-of select="titel"/>
					<xsl:text> </xsl:text>
				</xsl:if>
				<xsl:value-of select="vorname"/>
				<xsl:text> </xsl:text>
				<xsl:value-of select="nachname"/>
				<xsl:if test="titel2">
					<xsl:text>, </xsl:text>
					<xsl:value-of select="titel2"/>
				</xsl:if>
				<br/>
			</td>
			<td bgcolor="#EEEEEE">
				<xsl:if test="telefon">
					<xsl:value-of select="telefon"/>
				</xsl:if>
				<br/>
			</td>
			<td bgcolor="#EEEEEE">
				<xsl:if test="email">
					<xsl:value-of select="email"/>
				</xsl:if>
				<br/>
			</td>
		</tr>
	</xsl:for-each>
</xsl:template>


<xsl:template name="showseminar">
	<xsl:for-each select="seminar">
		<tr border="0" align="left">
			<td bgcolor="#EEEEEE">
				<xsl:for-each select="dozenten/dozent">
					<xsl:if test="position() &gt; 1">
						<xsl:text>, </xsl:text>
					</xsl:if>
					<xsl:value-of select="."/>
				</xsl:for-each>
			</td>
			<td bgcolor="#EEEEEE">
				<b>
					<xsl:value-of select="titel"/>
				</b>
			</td>
			<td bgcolor="#EEEEEE">
				<xsl:value-of select="status"/>
			</td>
			<td bgcolor="#EEEEEE">
				<xsl:value-of select="termine/termin"/>
			</td>
			<td bgcolor="#EEEEEE">
				<xsl:value-of select="raum"/>
			</td>
		</tr>
	</xsl:for-each>
</xsl:template>
</xsl:stylesheet>
