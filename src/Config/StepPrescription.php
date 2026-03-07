<?php

/**
 * Enum StepPrescription
 *
 * Represents the various steps in a prescription workflow
 * for the Symfony application using SQLite database.
 *
 * This enumerator defines specific states:
 * - Open: Initial step where the prescription is created.
 * - Write: Step where the prescription is being written.
 * - ValidCase: Represents validation of the prescription case.
 * - GeneratePDF: Indicates the prescription is being converted to a PDF.
 * - Signed: Final step where the prescription is signed.
 */

namespace App\Config;

enum StepPrescription: string
{
    case Open = 'Open';
    case Write = 'Write';
    case TwoParts = 'TwoParts';
    case ValidCase = 'ValidCase';
    case GeneratePDF = 'GeneratePDF';
    case Signed = 'Signed';
}
