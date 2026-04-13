<?php

namespace App\Services\Contabilidad;

use App\Models\ConfiguracionHermandad;
use App\Models\Hermano;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Genera un XML pain.008.001.02 (adeudo directo CORE) orientado a remesas de cuotas.
 * El identificador de mandato y acreedor deben cumplir la normativa del banco presentador.
 */
class SepaPain008Generator
{
    /**
     * @param  Collection<int, Hermano>  $hermanos  Con IBAN válido
     */
    public function generar(
        Collection $hermanos,
        string $importeEurosPorHermano,
        string $conceptoCobro,
        ?string $fechaCobro = null,
    ): string {
        $config = ConfiguracionHermandad::query()->firstOrFail();
        $fechaCobro = $fechaCobro ?: now()->addDays(5)->format('Y-m-d');
        $creDtTm = Carbon::now()->format('Y-m-d\TH:i:s');
        $msgId = 'MSG-'.Str::upper(Str::random(16));
        $pmtInfId = 'PMT-'.Str::upper(Str::random(12));
        $nbOfTxs = (string) $hermanos->count();
        $importeFloat = (float) $importeEurosPorHermano;
        $ctrlSum = number_format($hermanos->count() * $importeFloat, 2, '.', '');

        $nombreAcreedor = htmlspecialchars($config->nombre_hermandad ?? $config->nombre_corto ?? 'Hermandad', ENT_XML1);
        $ibanAcreedor = preg_replace('/\s+/', '', (string) $config->iban_cuotas);
        $bicAcreedor = $config->bic_swift ? preg_replace('/\s+/', '', (string) $config->bic_swift) : '';
        $creditorId = preg_replace('/\s+/', '', (string) ($config->cif ?? ''));
        if ($creditorId === '') {
            $creditorId = 'ES00000UNKNOWN';
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<Document xmlns="urn:iso:std:iso:20022:tech:xsd:pain.008.001.02" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">';
        $xml .= '<CstmrDrctDbtInitn>';
        $xml .= '<GrpHdr><MsgId>'.htmlspecialchars($msgId, ENT_XML1).'</MsgId><CreDtTm>'.$creDtTm.'</CreDtTm><NbOfTxs>'.$nbOfTxs.'</NbOfTxs><CtrlSum>'.$ctrlSum.'</CtrlSum>';
        $xml .= '<InitgPty><Nm>'.$nombreAcreedor.'</Nm></InitgPty>';
        $xml .= '</GrpHdr>';

        $xml .= '<PmtInf>';
        $xml .= '<PmtInfId>'.htmlspecialchars($pmtInfId, ENT_XML1).'</PmtInfId>';
        $xml .= '<PmtMtd>DD</PmtMtd>';
        $xml .= '<NbOfTxs>'.$nbOfTxs.'</NbOfTxs><CtrlSum>'.$ctrlSum.'</CtrlSum>';
        $xml .= '<PmtTpInf><SvcLvl><Cd>SEPA</Cd></SvcLvl><LclInstrm><Cd>CORE</Cd></LclInstrm><SeqTp>RCUR</SeqTp></PmtTpInf>';
        $xml .= '<ReqdColltnDt>'.$fechaCobro.'</ReqdColltnDt>';
        $xml .= '<Cdtr><Nm>'.$nombreAcreedor.'</Nm></Cdtr>';
        $xml .= '<CdtrAcct><Id><IBAN>'.$ibanAcreedor.'</IBAN></Id></CdtrAcct>';
        if ($bicAcreedor !== '') {
            $xml .= '<CdtrAgt><FinInstnId><BIC>'.$bicAcreedor.'</BIC></FinInstnId></CdtrAgt>';
        } else {
            $xml .= '<CdtrAgt><FinInstnId><Othr><Id>NOTPROVIDED</Id></Othr></FinInstnId></CdtrAgt>';
        }
        $xml .= '<ChrgBr>SLEV</ChrgBr>';
        $xml .= '<CdtrSchmeId><Id><PrvtId><Othr><Id>'.htmlspecialchars($creditorId, ENT_XML1).'</Id><SchmeNm><Prtry>SEPA</Prtry></SchmeNm></Othr></PrvtId></Id></CdtrSchmeId>';

        $importeUnidad = number_format($importeFloat, 2, '.', '');
        $idx = 0;

        foreach ($hermanos as $hermano) {
            $idx++;
            $endToEndId = 'E2E-'.$hermano->id.'-'.$idx;
            $ibanDeudor = preg_replace('/\s+/', '', (string) $hermano->iban);
            $nombreDeudor = htmlspecialchars(trim($hermano->titular_cuenta ?: $hermano->nombre.' '.$hermano->apellidos), ENT_XML1);
            $mndtId = 'MNDT-H-'.$hermano->id;
            $dtSgntr = $hermano->fecha_alta?->format('Y-m-d') ?? $fechaCobro;

            $xml .= '<DrctDbtTxInf>';
            $xml .= '<PmtId><EndToEndId>'.htmlspecialchars($endToEndId, ENT_XML1).'</EndToEndId></PmtId>';
            $xml .= '<InstdAmt Ccy="EUR">'.$importeUnidad.'</InstdAmt>';
            $xml .= '<DrctDbtTx><MndtRltdInf><MndtId>'.htmlspecialchars($mndtId, ENT_XML1).'</MndtId><DtOfSgntr>'.$dtSgntr.'</DtOfSgntr></MndtRltdInf></DrctDbtTx>';
            $xml .= '<DbtrAgt><FinInstnId><Othr><Id>NOTPROVIDED</Id></Othr></FinInstnId></DbtrAgt>';
            $xml .= '<Dbtr><Nm>'.$nombreDeudor.'</Nm></Dbtr>';
            $xml .= '<DbtrAcct><Id><IBAN>'.$ibanDeudor.'</IBAN></Id></DbtrAcct>';
            $xml .= '<RmtInf><Ustrd>'.htmlspecialchars($conceptoCobro, ENT_XML1).'</Ustrd></RmtInf>';
            $xml .= '</DrctDbtTxInf>';
        }

        $xml .= '</PmtInf>';
        $xml .= '</CstmrDrctDbtInitn></Document>';

        return $xml;
    }

    /**
     * Remesa con importes variables por recibo (periodicidad / atrasos).
     *
     * @param  Collection<int, array{hermano: Hermano, importe: float, end_to_end_id: string, concepto: string}>  $lineas
     * @return array{xml: string, msg_id: string, pmt_inf_id: string, ctrl_sum: string}
     */
    public function generarMultilinea(Collection $lineas, string $fechaCobro, string $conceptoGrupo): array
    {
        if ($lineas->isEmpty()) {
            throw new \InvalidArgumentException('La remesa no tiene líneas.');
        }

        $config = ConfiguracionHermandad::query()->firstOrFail();
        $creDtTm = Carbon::now()->format('Y-m-d\TH:i:s');
        $msgId = 'MSG-'.Str::upper(Str::random(16));
        $pmtInfId = 'PMT-'.Str::upper(Str::random(12));
        $nbOfTxs = (string) $lineas->count();
        $ctrlSum = number_format($lineas->sum(fn (array $r) => (float) $r['importe']), 2, '.', '');

        $nombreAcreedor = htmlspecialchars($config->nombre_hermandad ?? $config->nombre_corto ?? 'Hermandad', ENT_XML1);
        $ibanAcreedor = preg_replace('/\s+/', '', (string) $config->iban_cuotas);
        $bicAcreedor = $config->bic_swift ? preg_replace('/\s+/', '', (string) $config->bic_swift) : '';
        $creditorId = preg_replace('/\s+/', '', (string) ($config->cif ?? ''));
        if ($creditorId === '') {
            $creditorId = 'ES00000UNKNOWN';
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<Document xmlns="urn:iso:std:iso:20022:tech:xsd:pain.008.001.02" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">';
        $xml .= '<CstmrDrctDbtInitn>';
        $xml .= '<GrpHdr><MsgId>'.htmlspecialchars($msgId, ENT_XML1).'</MsgId><CreDtTm>'.$creDtTm.'</CreDtTm><NbOfTxs>'.$nbOfTxs.'</NbOfTxs><CtrlSum>'.$ctrlSum.'</CtrlSum>';
        $xml .= '<InitgPty><Nm>'.$nombreAcreedor.'</Nm></InitgPty>';
        $xml .= '</GrpHdr>';

        $xml .= '<PmtInf>';
        $xml .= '<PmtInfId>'.htmlspecialchars($pmtInfId, ENT_XML1).'</PmtInfId>';
        $xml .= '<PmtMtd>DD</PmtMtd>';
        $xml .= '<NbOfTxs>'.$nbOfTxs.'</NbOfTxs><CtrlSum>'.$ctrlSum.'</CtrlSum>';
        $xml .= '<PmtTpInf><SvcLvl><Cd>SEPA</Cd></SvcLvl><LclInstrm><Cd>CORE</Cd></LclInstrm><SeqTp>RCUR</SeqTp></PmtTpInf>';
        $xml .= '<ReqdColltnDt>'.$fechaCobro.'</ReqdColltnDt>';
        $xml .= '<Cdtr><Nm>'.$nombreAcreedor.'</Nm></Cdtr>';
        $xml .= '<CdtrAcct><Id><IBAN>'.$ibanAcreedor.'</IBAN></Id></CdtrAcct>';
        if ($bicAcreedor !== '') {
            $xml .= '<CdtrAgt><FinInstnId><BIC>'.$bicAcreedor.'</BIC></FinInstnId></CdtrAgt>';
        } else {
            $xml .= '<CdtrAgt><FinInstnId><Othr><Id>NOTPROVIDED</Id></Othr></FinInstnId></CdtrAgt>';
        }
        $xml .= '<ChrgBr>SLEV</ChrgBr>';
        $xml .= '<CdtrSchmeId><Id><PrvtId><Othr><Id>'.htmlspecialchars($creditorId, ENT_XML1).'</Id><SchmeNm><Prtry>SEPA</Prtry></SchmeNm></Othr></PrvtId></Id></CdtrSchmeId>';

        foreach ($lineas as $row) {
            /** @var Hermano $hermano */
            $hermano = $row['hermano'];
            $endToEndId = (string) $row['end_to_end_id'];
            $importeFloat = round((float) $row['importe'], 2);
            $conceptoLinea = (string) $row['concepto'];
            $ibanDeudor = preg_replace('/\s+/', '', (string) $hermano->iban);
            $nombreDeudor = htmlspecialchars(trim($hermano->titular_cuenta ?: $hermano->nombre.' '.$hermano->apellidos), ENT_XML1);
            $mndtId = 'MNDT-H-'.$hermano->id;
            $dtSgntr = $hermano->fecha_alta?->format('Y-m-d') ?? $fechaCobro;
            $importeUnidad = number_format($importeFloat, 2, '.', '');

            $xml .= '<DrctDbtTxInf>';
            $xml .= '<PmtId><EndToEndId>'.htmlspecialchars($endToEndId, ENT_XML1).'</EndToEndId></PmtId>';
            $xml .= '<InstdAmt Ccy="EUR">'.$importeUnidad.'</InstdAmt>';
            $xml .= '<DrctDbtTx><MndtRltdInf><MndtId>'.htmlspecialchars($mndtId, ENT_XML1).'</MndtId><DtOfSgntr>'.$dtSgntr.'</DtOfSgntr></MndtRltdInf></DrctDbtTx>';
            $xml .= '<DbtrAgt><FinInstnId><Othr><Id>NOTPROVIDED</Id></Othr></FinInstnId></DbtrAgt>';
            $xml .= '<Dbtr><Nm>'.$nombreDeudor.'</Nm></Dbtr>';
            $xml .= '<DbtrAcct><Id><IBAN>'.$ibanDeudor.'</IBAN></Id></DbtrAcct>';
            $xml .= '<RmtInf><Ustrd>'.htmlspecialchars($conceptoGrupo.' — '.$conceptoLinea, ENT_XML1).'</Ustrd></RmtInf>';
            $xml .= '</DrctDbtTxInf>';
        }

        $xml .= '</PmtInf>';
        $xml .= '</CstmrDrctDbtInitn></Document>';

        return [
            'xml' => $xml,
            'msg_id' => $msgId,
            'pmt_inf_id' => $pmtInfId,
            'ctrl_sum' => $ctrlSum,
        ];
    }
}
