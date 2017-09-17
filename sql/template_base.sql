/*
 * Copyright 2012-2017 Infium AB
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

START TRANSACTION;

SET @databaseVersion = '1.1.0';

CREATE TABLE `Article` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Active` tinyint(1) NOT NULL,
  `Number` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `TaxGroup` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `Number` (`Number`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE `AuditTrail` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Table` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `TableId` int(11) NOT NULL,
  `Operation` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `Data` text COLLATE utf8_unicode_ci,
  `Time` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `User` int(11) NOT NULL,
  `IP` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE `Customer` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Active` tinyint(1) NOT NULL,
  `Number` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `InternalName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `EmailInvoice` tinyint(1) NOT NULL,
  `TaxGroup` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `TaxNumber` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `PaymentTerms` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `BillToAddressLine1` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `BillToAddressLine2` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `BillToAddressLine3` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `BillToAddressLine4` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `BillToAddressCity` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `BillToAddressStateOrProvince` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `BillToAddressZipOrPostalCode` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `BillToAddressCountry` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ShipToAddressLine1` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ShipToAddressLine2` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ShipToAddressLine3` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ShipToAddressLine4` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ShipToAddressCity` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ShipToAddressStateOrProvince` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ShipToAddressZipOrPostalCode` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ShipToAddressCountry` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `Number` (`Number`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE `CustomerInvoice` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Number` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `BookingDate` date NOT NULL,
  `DateDue` date NOT NULL,
  `CustomerId` int(11) NOT NULL,
  `CustomerReference` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `PaymentReference` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `PaymentTerms` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `TaxNumber` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `BillToAddressLine1` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `BillToAddressLine2` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `BillToAddressLine3` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `BillToAddressLine4` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `BillToAddressCity` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `BillToAddressStateOrProvince` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `BillToAddressZipOrPostalCode` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `BillToAddressCountry` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ShipToAddressLine1` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ShipToAddressLine2` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ShipToAddressLine3` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ShipToAddressLine4` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ShipToAddressCity` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ShipToAddressStateOrProvince` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ShipToAddressZipOrPostalCode` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ShipToAddressCountry` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `AmountNet` decimal(19,2) NOT NULL,
  `AmountTax` decimal(19,2) NOT NULL,
  `AmountGross` decimal(19,2) NOT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `Number` (`Number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE `CustomerInvoiceRow` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `ParentId` int(11) NOT NULL,
  `Number` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ArticleId` int(11) NOT NULL,
  `Quantity` decimal(19,2) NOT NULL,
  `Price` decimal(19,2) NOT NULL,
  `TaxPercent` decimal(19,4) NOT NULL,
  `TaxCode` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `AmountNet` decimal(19,2) NOT NULL,
  `AmountTax` decimal(19,2) NOT NULL,
  `AmountGross` decimal(19,2) NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE `CustomerPayment` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Number` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `BookingDate` date NOT NULL,
  `PartnerDate` date NOT NULL,
  `AccountNumber` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `Amount` decimal(19,2) NOT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `Number` (`Number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE `CustomerPaymentRow` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `ParentId` int(11) NOT NULL,
  `Number` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `PaymentReference` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Amount` decimal(19,2) NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE `GeneralLedgerAccount` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Year` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `AccountNumber` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `Description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Type` char(2) COLLATE utf8_unicode_ci NOT NULL,
  `SubAccountNumber` tinyint(1) NOT NULL,
  `ShowInVendorInvoice` tinyint(1) NOT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `DuplicationFilter` (`Year`,`AccountNumber`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE `GeneralLedgerAccountBalance` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Year` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `BookingDate` date DEFAULT NULL,
  `AccountNumber` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `SubAccountNumber` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ProfitCenter` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `Debit` decimal(19,2) NOT NULL,
  `Credit` decimal(19,2) NOT NULL,
  `Amount` decimal(19,2) NOT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `DuplicationFilter` (`Year`,`BookingDate`,`AccountNumber`,`SubAccountNumber`,`ProfitCenter`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE `GeneralLedgerAccountBooking` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Number` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Year` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `BookingDate` date NOT NULL,
  `Text` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `DocumentType` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `DocumentTypeNumber` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `Number` (`Number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE `GeneralLedgerAccountBookingRow` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `ParentId` int(11) NOT NULL,
  `Number` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Year` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `BookingDate` date NOT NULL,
  `AccountNumber` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `SubAccountNumber` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `BalanceNoteBreakdown` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ProfitCenter` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `Text` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Debit` decimal(19,2) NOT NULL,
  `Credit` decimal(19,2) NOT NULL,
  `Amount` decimal(19,2) NOT NULL,
  `TaxCode` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `TaxNumber` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `DocumentType` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `DocumentTypeNumber` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ClearingReference` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ClearingDate` date DEFAULT NULL,
  `ClearingNumber` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE `GeneralLedgerAccountClearing` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Number` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `BookingDate` date NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE `GeneralLedgerAccountClearingRow` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `ParentId` int(11) NOT NULL,
  `Number` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `BookingRowId` int(11) NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE `GeneralLedgerAccountDeterminationInvoiceRow` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `TaxRuleSet` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `FromDate` date NOT NULL,
  `ToDate` date NOT NULL,
  `Type` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `TaxGroupCustomerOrVendor` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `TaxGroupArticleOrAccount` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `AccountArticle` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `AccountArticleTaxCode` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `TaxPercent` decimal(19,4) NOT NULL,
  `AccountTaxOutput` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `AccountTaxOutputTaxCode` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `AccountTaxInput` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `AccountTaxInputTaxCode` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `DuplicationFilter` (`TaxRuleSet`,`FromDate`,`ToDate`,`Type`,`TaxGroupCustomerOrVendor`,`TaxGroupArticleOrAccount`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE `GeneralLedgerTaxGroupArticleOrAccount` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `TaxRuleSet` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Order` int(11) NOT NULL,
  `TaxGroup` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE `GeneralLedgerTaxGroupCustomerOrVendor` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `TaxRuleSet` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Order` int(11) NOT NULL,
  `TaxGroup` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE `GeneralLedgerYear` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Year` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `Status` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `Year` (`Year`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE `Number` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Prefix` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `LastNumber` int(11) NOT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `Type` (`Type`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

INSERT INTO `Number` (`Type`, `Prefix`, `LastNumber`) VALUES
('Customer', '', 1000),
('Vendor', '', 2000),
('CustomerInvoice', '', 30000),
('CustomerPayment', '', 40000),
('VendorInvoice', '', 50000),
('VendorPaymentList', '', 60000),
('VendorPaymentCompleted', '', 70000),
('GeneralLedgerAccountBooking', '', 80000),
('TaxReport', '', 90000),
('GeneralLedgerAccountClearing', '', 100000);

CREATE TABLE `Property` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Property` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Value` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `ValueArray` varchar(1024) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ReadOnly` tinyint(1) NOT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `Property` (`Property`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

INSERT INTO `Property` (`Property`, `Value`, `ValueArray`, `ReadOnly`) VALUES
('Currency', '', NULL, 1),
('DocumentFoot', '', NULL, 0),
('TaxRuleSet', '', NULL, 1),
('DatabaseVersion', @databaseVersion, NULL, 1),
('CompanyName', '', NULL, 0);

CREATE TABLE `ReportTemplate` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Year` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `Type` char(2) COLLATE utf8_unicode_ci NOT NULL,
  `Code` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `YearTypeDescription` (`Year`,`Type`,`Description`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE `ReportTemplateRow` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `ParentId` int(11) NOT NULL,
  `ParentSection` int(11) DEFAULT NULL,
  `Order` int(11) NOT NULL,
  `SectionDescription` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AccountNumber` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE `TaxField` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `TaxRuleSet` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `FromDate` date NOT NULL,
  `ToDate` date NOT NULL,
  `Order` int(11) NOT NULL,
  `Field` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `DuplicationFilter` (`TaxRuleSet`,`FromDate`,`ToDate`,`Field`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE `TaxFieldCalculation` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `TaxRuleSet` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `FromDate` date NOT NULL,
  `ToDate` date NOT NULL,
  `Field` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `TaxCode` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `MoveFromAccount` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `MoveFromAccountTaxCode` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `MoveToAccount` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `MoveToAccountTaxCode` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ReversedSignInReport` tinyint(1) NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE `TaxReport` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Number` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `BookingDate` date NOT NULL,
  `FromDate` date NOT NULL,
  `ToDate` date NOT NULL,
  `TaxRuleSet` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Active` tinyint(1) NOT NULL,
  `Reversal` tinyint(1) NOT NULL,
  `Reversed` tinyint(1) NOT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `Number` (`Number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE `TaxReportRegionRow` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `ParentId` int(11) NOT NULL,
  `Number` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Region` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Type` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `ProductOrService` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `TaxNumber` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Amount` decimal(19,2) NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE `TaxReportRow` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `ParentId` int(11) NOT NULL,
  `Number` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Field` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Amount` decimal(19,2) NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE `User` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `PasswordSalt` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `PasswordEncrypted` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Access` varchar(4096) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `Username` (`Username`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

INSERT INTO `User` (`Name`, `Username`, `Email`, `PasswordSalt`, `PasswordEncrypted`, `Access`) VALUES
('User', 'user', '*', 'b0f00ae100bd7308135fdd0f06f41e5c', '364f32ad9de66c42941ecb2aedd0a8b9', '["AdministrationArticleDatabase","AdministrationChartOfAccountsAccountChange","AdministrationChartOfAccountsAccountCreate","AdministrationChartOfAccountsBalanceCarryForward","AdministrationChartOfAccountsReportTemplateChange","AdministrationChartOfAccountsReportTemplateCreate","AdministrationChartOfAccountsYearChange","AdministrationChartOfAccountsYearCreate","AdministrationCustomerDatabase","AdministrationProperty","AdministrationUserDatabaseAccessChange","AdministrationUserDatabaseCreate","AdministrationUserDatabasePasswordChange","AdministrationUserDatabaseView","AdministrationVendorDatabase","CustomerInvoiceCreate","CustomerInvoiceEmail","CustomerInvoiceReverse","CustomerInvoiceView","CustomerPaymentCreate","CustomerPaymentReverse","CustomerPaymentView","GeneralLedgerJournalVoucherCreate","GeneralLedgerJournalVoucherView","ReportBalanceSheet","ReportGeneralLedger","ReportProfitAndLoss","ReportTax","VendorInvoiceCreate","VendorInvoiceReverse","VendorInvoiceView","VendorPaymentCompletedCreate","VendorPaymentCompletedReverse","VendorPaymentCompletedView","VendorPaymentListCreate","VendorPaymentListReverse","VendorPaymentListView"]');

CREATE TABLE `UserAccessAvailible` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `ResourceName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Active` tinyint(1) NOT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `ResourceName` (`ResourceName`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

INSERT INTO `UserAccessAvailible` (`ResourceName`, `Active`) VALUES
('AdministrationUserDatabaseCreate', 1),
('AdministrationUserDatabaseView', 1),
('AdministrationUserDatabaseAccessChange', 1),
('AdministrationCustomerDatabase', 1),
('AdministrationVendorDatabase', 1),
('CustomerPaymentCreate', 1),
('CustomerPaymentView', 1),
('GeneralLedgerJournalVoucherCreate', 1),
('GeneralLedgerClearingCreate', 1),
('GeneralLedgerClearingView', 1),
('ReportProfitAndLoss', 1),
('ReportBalanceSheet', 1),
('VendorPaymentListCreate', 1),
('VendorPaymentListView', 1),
('VendorPaymentCompletedCreate', 1),
('VendorPaymentCompletedView', 1),
('CustomerPaymentReverse', 1),
('VendorPaymentListReverse', 1),
('VendorPaymentCompletedReverse', 1),
('AdministrationProperty', 1),
('CustomerInvoiceView', 1),
('CustomerInvoiceCreate', 1),
('CustomerInvoiceReverse', 1),
('VendorInvoiceView', 1),
('VendorInvoiceCreate', 1),
('VendorInvoiceReverse', 1),
('AdministrationChartOfAccountsBalanceCarryForward', 1),
('AdministrationChartOfAccountsYearChange', 1),
('AdministrationChartOfAccountsYearCreate', 1),
('AdministrationChartOfAccountsAccountCreate', 1),
('AdministrationChartOfAccountsAccountChange', 1),
('AdministrationUserDatabasePasswordChange', 1),
('AdministrationArticleDatabase', 1),
('ReportTax', 1),
('ReportAuditTrail', 1),
('CustomerInvoiceEmail', 1),
('ReportGeneralLedger', 1),
('GeneralLedgerJournalVoucherView', 1),
('AdministrationChartOfAccountsReportTemplateChange', 1),
('AdministrationChartOfAccountsReportTemplateCreate', 1);

CREATE TABLE `UserToken` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `UserId` int(11) NOT NULL,
  `Token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ClientPlatform` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ClientPlatformVersion` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ClientPlatformDevice` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ClientAppVersion` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE `Vendor` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Active` tinyint(1) NOT NULL,
  `Number` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `InternalName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `BankAccount` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `TaxGroup` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `TaxNumber` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `PaymentTerms` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `BillFromAddressLine1` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `BillFromAddressLine2` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `BillFromAddressLine3` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `BillFromAddressLine4` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `BillFromAddressCity` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `BillFromAddressStateOrProvince` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `BillFromAddressZipOrPostalCode` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `BillFromAddressCountry` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `Number` (`Number`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE `VendorInvoice` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Number` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ReversalId` int(11) DEFAULT NULL,
  `BookingDate` date NOT NULL,
  `PartnerDate` date NOT NULL,
  `DueDate` date NOT NULL,
  `VendorId` int(11) NOT NULL,
  `InternalName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `BankAccount` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `PaymentReference` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `PaymentTerms` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `BillFromAddressLine1` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `BillFromAddressLine2` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `BillFromAddressLine3` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `BillFromAddressLine4` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `BillFromAddressCity` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `BillFromAddressStateOrProvince` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `BillFromAddressZipOrPostalCode` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `BillFromAddressCountry` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `AmountNet` decimal(19,2) NOT NULL,
  `AmountTax` decimal(19,2) NOT NULL,
  `AmountGross` decimal(19,2) NOT NULL,
  `AmountGrossRemaining` decimal(19,2) NOT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `Number` (`Number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE `VendorInvoiceRow` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `ParentId` int(11) NOT NULL,
  `Number` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `PreviousDocumentType` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `PreviousRowId` int(11) DEFAULT NULL,
  `Account` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ProfitCenter` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Amount` decimal(19,2) DEFAULT NULL,
  `TaxInclusionGroup` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TaxPercent` decimal(19,4) NOT NULL,
  `AmountNet` decimal(19,2) NOT NULL,
  `AmountTax` decimal(19,2) NOT NULL,
  `AmountGross` decimal(19,2) NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE `VendorPaymentCompleted` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Number` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `BookingDate` date NOT NULL,
  `PartnerDate` date NOT NULL,
  `AccountNumber` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `Amount` decimal(19,2) NOT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `Number` (`Number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE `VendorPaymentCompletedRow` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `ParentId` int(11) NOT NULL,
  `Number` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `PreviousDocumentType` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `PreviousRowId` int(11) NOT NULL,
  `PaymentReferenceOurSide` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `PaymentReferencePartnerSide` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Amount` decimal(19,2) NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE `VendorPaymentList` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Number` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `BookingDate` date NOT NULL,
  `Amount` decimal(19,2) NOT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `Number` (`Number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE `VendorPaymentListRow` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `ParentId` int(11) NOT NULL,
  `Number` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ReversalRowId` int(11) DEFAULT NULL,
  `PreviousDocumentType` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `PreviousRowId` int(11) NOT NULL,
  `BankAccount` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `InternalName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `PaymentReference` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `DueDate` date NOT NULL,
  `Amount` decimal(19,2) NOT NULL,
  `AmountRemaining` decimal(19,2) NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

COMMIT;