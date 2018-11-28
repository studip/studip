<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
	<xsl:output method="xml"/>
	<xsl:template match="/">
		<fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<fo:layout-master-set>
				<fo:simple-page-master master-name="cover" page-height="29.7cm" page-width="21cm" margin-top="0.5cm" margin-bottom="0.5cm" margin-left="1cm" margin-right="0.5cm">
					<fo:region-body margin-top="3cm"/>
				</fo:simple-page-master>
				<fo:simple-page-master master-name="leftPage" page-height="29.7cm" page-width="21cm" margin-left="0.5cm" margin-right="1.5cm" margin-top="0.5cm" margin-bottom="0.5cm">
					<fo:region-body margin-top="2.5cm" margin-bottom="2.5cm"/>
					<fo:region-before extent="2cm"/>
					<fo:region-after extent="2cm"/>
				</fo:simple-page-master>
				<fo:simple-page-master master-name="rightPage" page-height="29.7cm" page-width="21cm" margin-left="1.5cm" margin-right="0.5cm" margin-top="0.5cm" margin-bottom="0.5cm">
					<fo:region-body margin-top="2.5cm" margin-bottom="2.5cm"/>
					<fo:region-before extent="2cm"/>
					<fo:region-after extent="2cm"/>
				</fo:simple-page-master>
				<fo:page-sequence-master master-name="contents">
					<fo:repeatable-page-master-alternatives>
						<fo:conditional-page-master-reference master-reference="leftPage" odd-or-even="even"/>
						<fo:conditional-page-master-reference master-reference="rightPage" odd-or-even="odd"/>
					</fo:repeatable-page-master-alternatives>
				</fo:page-sequence-master>
			</fo:layout-master-set>
			<fo:page-sequence master-reference="cover">
				<fo:flow flow-name="xsl-region-body">
					<fo:block font-family="Helvetica" font-size="36pt" text-align="center">
						<xsl:if test="studip/institut/seminare">
Vorlesungskommentar
		</xsl:if>
						<xsl:if test="studip/institut/personen">
MitarbeiterInnenliste
		</xsl:if>
					</fo:block>
					<xsl:for-each select="studip">
						<fo:block font-family="Helvetica" font-size="24pt" text-align="center" space-after="17cm">
							<xsl:value-of select="@range"/>
						</fo:block>
						<fo:block text-align="center" font-family="Helvetica" font-size="20pt">
							<xsl:value-of select="@zeitraum"/>
						</fo:block>
						<fo:block text-align="center" font-family="Helvetica" font-size="18pt">
							<xsl:value-of select="@uni"/>
						</fo:block>
						<fo:block text-align="right" font-family="Helvetica" font-size="10pt">
							Generiert von Stud.IP Version <xsl:value-of select="@version"/>
						</fo:block>
					</xsl:for-each>
				</fo:flow>
			</fo:page-sequence>
			<fo:page-sequence master-reference="contents" initial-page-number="2">
				<fo:static-content flow-name="xsl-region-before">
					<fo:block font-family="Helvetica" font-size="10pt" text-align="center" border-style="solid" border-color="black">
		Inhaltsverzeichnis
				</fo:block>
				</fo:static-content>
				<fo:static-content flow-name="xsl-region-after">
					<fo:block font-family="Helvetica" font-size="10pt" text-align="center">
		Seite <fo:page-number/>
					</fo:block>
				</fo:static-content>
				<fo:flow flow-name="xsl-region-body">
					<fo:block text-align="justify">
						<fo:block font-size="14pt">
							<fo:inline font-weight="bold">Inhaltsverzeichnis</fo:inline>
						</fo:block>
						<xsl:for-each select="studip/institut">
							<fo:block space-after="10pt">
						</fo:block>
							<fo:block font-size="12pt" text-align-last="justify">
								<fo:inline font-weight="bold">
									<xsl:value-of select="name"/>
									<fo:leader leader-pattern="space"/>
									<fo:page-number-citation ref-id="{generate-id()}"/>
								</fo:inline>
							</fo:block>
							<xsl:choose>
								<xsl:when test="seminare/gruppe">
									<xsl:for-each select="seminare/gruppe">
										<fo:block font-size="10pt" text-align-last="justify">
											<fo:inline font-weight="bold">
												<xsl:value-of select="@key"/>
												<fo:leader leader-pattern="space"/>
												<fo:page-number-citation ref-id="{generate-id()}"/>
											</fo:inline>
										</fo:block>
										<xsl:choose>
											<xsl:when test="untergruppe">
												<xsl:for-each select="untergruppe">
													<fo:block font-size="10pt" text-align-last="justify">
														<fo:inline font-weight="bold">
															<xsl:value-of select="@key"/>
															<fo:leader leader-pattern="space"/>
															<fo:page-number-citation ref-id="{generate-id()}"/>
														</fo:inline>
													</fo:block>
													<xsl:for-each select="seminar">
														<fo:block font-size="10pt" text-align-last="justify">
															<xsl:value-of select="titel"/>
															<fo:leader leader-pattern="space"/>
															<fo:page-number-citation ref-id="{generate-id()}"/>
														</fo:block>
													</xsl:for-each>
												</xsl:for-each>
											</xsl:when>
											<xsl:otherwise>
												<xsl:for-each select="seminar">
													<fo:block font-size="10pt">
														<xsl:value-of select="titel"/>
														<fo:leader leader-pattern="space"/>
														<fo:page-number-citation ref-id="{generate-id()}"/>
													</fo:block>
												</xsl:for-each>
											</xsl:otherwise>
										</xsl:choose>
									</xsl:for-each>
								</xsl:when>
								<xsl:otherwise>
									<xsl:for-each select="seminare/seminar">
										<fo:block font-size="10pt">
											<xsl:value-of select="titel"/>
											<fo:leader leader-pattern="space"/>
											<fo:page-number-citation ref-id="{generate-id()}"/>
										</fo:block>
									</xsl:for-each>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:for-each>
					</fo:block>
				</fo:flow>
			</fo:page-sequence>
			<xsl:for-each select="studip/institut">
				<fo:page-sequence master-reference="contents">
					<fo:static-content flow-name="xsl-region-before">
						<fo:block font-family="Helvetica" font-size="10pt" text-align="center" border-style="solid" border-color="black">
							<xsl:if test="seminare">
		Vorlesungskommentar
		</xsl:if>
						</fo:block>
					</fo:static-content>
					<fo:static-content flow-name="xsl-region-after">
						<fo:block font-family="Helvetica" font-size="10pt" text-align="center">
		Seite <fo:page-number/>
						</fo:block>
					</fo:static-content>
					<fo:flow flow-name="xsl-region-body">
						<fo:block text-align="left" font-size="12pt" id="{generate-id()}">
							<fo:block text-align="center" font-size="16pt" space-after="10pt">
								<fo:inline font-weight="bold">
									<xsl:value-of select="name"/>
								</fo:inline>
							</fo:block>
<xsl:if test="fakultaet">
							<fo:block>
								<fo:inline font-weight="bold">Fakult&#228;t: </fo:inline>
								<xsl:value-of select="fakultaet"/>
							</fo:block>
</xsl:if>
<xsl:if test="homepage">
							<fo:block>
								<fo:inline font-weight="bold">Homepage: </fo:inline>
								<xsl:value-of select="homepage"/>
							</fo:block>
</xsl:if>
<xsl:if test="strasse">
							<fo:block>
								<fo:inline font-weight="bold">Strasse: </fo:inline>
								<xsl:value-of select="strasse"/>
							</fo:block>
</xsl:if>
<xsl:if test="plz">
							<fo:block>
								<fo:inline font-weight="bold">Postleitzahl: </fo:inline>
								<xsl:value-of select="plz"/>
							</fo:block>
</xsl:if>
<xsl:if test="telefon">
							<fo:block>
								<fo:inline font-weight="bold">Telefon: </fo:inline>
								<xsl:value-of select="telefon"/>
							</fo:block>
</xsl:if>
<xsl:if test="fax">
							<fo:block>
								<fo:inline font-weight="bold">Fax: </fo:inline>
								<xsl:value-of select="fax"/>
							</fo:block>
</xsl:if>
<xsl:if test="email">
							<fo:block>
								<fo:inline font-weight="bold">E-mail: </fo:inline>
								<xsl:value-of select="email"/>
							</fo:block>
</xsl:if>
<xsl:if test="datenfelder">
	<xsl:for-each select="datenfelder/datenfeld">
							<fo:block>
								<fo:inline font-weight="bold"><xsl:value-of select="@key"/>: </fo:inline>
								<xsl:value-of select="."/>
							</fo:block>
	</xsl:for-each>
</xsl:if>
							<fo:block space-after="12pt">
							</fo:block>
							<xsl:if test="seminare">
								<fo:block text-align="center" font-size="14pt" space-after="10pt">     
	Veranstaltungen
</fo:block>
								<xsl:choose>
									<xsl:when test="seminare/gruppe">
										<xsl:for-each select="seminare/gruppe">
											<fo:block text-align="justify" font-size="16pt" space-after="6pt" id="{generate-id()}">
												<fo:inline font-weight="bold">
													<xsl:value-of select="@key"/>
												</fo:inline>
											</fo:block>
											<xsl:choose>
												<xsl:when test="untergruppe">
													<xsl:for-each select="untergruppe">
														<fo:block text-align="justify" font-size="14pt" space-after="6pt" id="{generate-id()}">
															<fo:inline font-weight="bold">
																<xsl:value-of select="@key"/>
															</fo:inline>
															<fo:block>
																<xsl:text> 
 </xsl:text>
															</fo:block>
														</fo:block>
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
						</fo:block>
					</fo:flow>
				</fo:page-sequence>
			</xsl:for-each>
		</fo:root>
	</xsl:template>

	<xsl:template name="showseminar">
		<xsl:for-each select="seminar">
			<fo:block text-align="justify" font-size="14pt" border-style="solid" border-color="black" space-after="6pt" id="{generate-id()}">
				<xsl:for-each select="dozenten/dozent">
					<xsl:if test="position() &gt; 1">
						<xsl:text>, </xsl:text>
					</xsl:if>
					<xsl:value-of select="."/>
				</xsl:for-each>: <xsl:value-of select="titel"/>
			</fo:block>
			<xsl:if test="untertitel">
				<fo:block text-align="justify" font-size="12pt">
					<fo:inline font-weight="bold">Untertitel: </fo:inline>
					<xsl:value-of select="untertitel"/>
				</fo:block>
			</xsl:if>
			<fo:block text-align="justify" font-size="12pt">
				<fo:inline font-weight="bold">Lehrende: </fo:inline>
				<xsl:for-each select="dozenten/dozent">
					<xsl:if test="position() &gt; 1">
						<xsl:text>, </xsl:text>
					</xsl:if>
					<xsl:value-of select="."/>
				</xsl:for-each>
			</fo:block>
			<fo:block>
				<fo:inline font-weight="bold">Termin: </fo:inline>
				<xsl:value-of select="termine/termin"/>
			</fo:block>
			<fo:block>
				<fo:inline font-weight="bold">Erster Termin: </fo:inline>
				<xsl:value-of select="termine/erstertermin"/>
			</fo:block>
			<xsl:if test="termine/vorbesprechung">
				<fo:block>
					<fo:inline font-weight="bold">Vorbesprechung: </fo:inline>
					<xsl:value-of select="termine/vorbesprechung"/>
				</fo:block>
			</xsl:if>
			<xsl:text>
</xsl:text>
			<xsl:if test="status">
				<fo:block>
					<fo:inline font-weight="bold">Status: </fo:inline>
					<xsl:value-of select="status"/>
				</fo:block>
			</xsl:if>
			<xsl:if test="veranstaltungsnummer">
				<fo:block>
					<fo:inline font-weight="bold">Veranstaltungsnummer: </fo:inline>
					<xsl:value-of select="veranstaltungsnummer"/>
				</fo:block>
			</xsl:if>
			<xsl:if test="beschreibung">
				<fo:block linefeed-treatment="preserve">
					<fo:inline font-weight="bold">Beschreibung: </fo:inline>
					<xsl:value-of select="beschreibung"/>
				</fo:block>
			</xsl:if>
			<xsl:if test="raum">
				<fo:block>
					<fo:inline font-weight="bold">Raum: </fo:inline>
					<xsl:value-of select="raum"/>
				</fo:block>
			</xsl:if>
			<xsl:if test="sonstiges">
				<fo:block>
					<fo:inline font-weight="bold">Sonstiges: </fo:inline>
					<xsl:value-of select="sonstiges"/>
				</fo:block>
			</xsl:if>
			<xsl:if test="art">
				<fo:block>
					<fo:inline font-weight="bold">Art der Veranstaltung: </fo:inline>
					<xsl:value-of select="art"/>
				</fo:block>
			</xsl:if>
			<xsl:if test="teilnehmer">
				<fo:block>
					<fo:inline font-weight="bold">Teilnahme: </fo:inline>
					<xsl:value-of select="teilnehmer"/>
				</fo:block>
			</xsl:if>
			<xsl:if test="teilnehmerzahl">
				<xsl:for-each select="teilnehmerzahl">
					<fo:block>
						<fo:inline font-weight="bold"><xsl:value-of select="@key"/> Anzahl Teilnehmende: </fo:inline>
						<xsl:value-of select="."/>
					</fo:block>
				</xsl:for-each>
			</xsl:if>
			<xsl:if test="voraussetzung">
				<fo:block>
					<fo:inline font-weight="bold">Voraussetzungen: </fo:inline>
					<xsl:value-of select="voraussetzung"/>
				</fo:block>
			</xsl:if>
			<xsl:if test="lernorga">
				<fo:block>
					<fo:inline font-weight="bold">Lernorganisation: </fo:inline>
					<xsl:value-of select="lernorga"/>
				</fo:block>
			</xsl:if>
			<xsl:if test="schein">
				<fo:block>
					<fo:inline font-weight="bold">Leistungsnachweis: </fo:inline>
					<xsl:value-of select="schein"/>
				</fo:block>
			</xsl:if>
			<xsl:if test="ects">
				<fo:block>
					<fo:inline font-weight="bold">ECTS: </fo:inline>
					<xsl:value-of select="ects"/>
				</fo:block>
			</xsl:if>
			<xsl:if test="bereiche">
				<fo:block>
					<fo:inline font-weight="bold">Bereich: </fo:inline>
				</fo:block>
				<xsl:for-each select="bereiche/bereich">
				<fo:block>
					<xsl:value-of select="."/>
				</fo:block>
				</xsl:for-each>
			</xsl:if>
			<xsl:if test="lvgruppen">
				<fo:block>
					<fo:inline font-weight="bold">Module: </fo:inline>
				</fo:block>
				<xsl:for-each select="lvgruppen/lvgruppe">
				<fo:block>
					<xsl:value-of select="."/>
				</fo:block>
				</xsl:for-each>
			</xsl:if>
			<xsl:if test="datenfelder">
				<xsl:for-each select="datenfelder/datenfeld">
					<fo:block>
						<fo:inline font-weight="bold"><xsl:value-of select="@key"/>: </fo:inline>
						<xsl:value-of select="."/>
					</fo:block>
				</xsl:for-each>
			</xsl:if>
			<fo:block space-after="12pt"> 
</fo:block>
		</xsl:for-each>
	</xsl:template>
</xsl:stylesheet>
