<?php

namespace App\Service;

use App\Entity\Categorias;
use App\Repository\ArbitrosRepository;
use App\Repository\FisicaRepository;
use App\Repository\TecnicosRepository;
use App\Repository\InformesRepository;
use App\Repository\AsistenciaRepository;
use App\Repository\EntrenamientosRepository;
use App\Repository\SimulacrosRepository;
use App\Repository\SancionesRepository;
use App\Repository\TestRepository;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ClasificacionService
{
    public function __construct(
        private readonly ArbitrosRepository   $arbRepo,
        private readonly FisicaRepository     $fisicaRepo,
        private readonly TecnicosRepository   $tecnicosRepo,
        private readonly InformesRepository   $informesRepo,
        private readonly AsistenciaRepository $asistRepo,
        private readonly EntrenamientosRepository $entrenosRepo,
        private readonly SimulacrosRepository $simusRepo,
        private readonly SancionesRepository  $sancionesRepo,
        private readonly TestRepository $testRepo
    ) {}

    private function calcularBonificacionFisico2(?float $yoyo, string $sexo): float
    {
        if ($yoyo === null) return 0.00;
        $yoyo = round($yoyo, 1);

        if ($sexo === 'MASCULINO') {
            return match ($yoyo) {
                17.1, 17.2 => 0.5, 17.3 => 1.0, 17.4 => 1.5, 17.5 => 2.0, 17.6 => 3.0,
                17.7 => 4.0, 17.8 => 5.0, 18.1 => 6.0, 18.2 => 7.0, 18.3 => 8.0,
                18.4 => 9.0, 18.5 => 10.0, 18.6 => 11.0, 18.7 => 12.0,
                18.8 => 13.0, 19.1 => 14.0, 19.2 => 15.0, default => 0.00
            };
        }

        if ($sexo === 'FEMENINO') {
            return match ($yoyo) {
                15.1 => 0.5, 15.2 => 1.0, 15.3 => 2.0, 15.4 => 3.0, 15.5 => 4.0,
                15.6 => 5.0, 15.7 => 6.0, 15.8 => 7.0, 16.1 => 8.0,
                16.2 => 9.0, 16.3 => 10.0, 16.4 => 11.0, 16.5 => 12.0,
                16.6 => 13.0, 16.7 => 14.0, 16.8 => 15.0,
                default => 0.00
            };
        }

        return 0.00;
    }

    private function calcularBonificacionFisico2_oficiales(?float $yoyo, string $sexo): float
    {
        if ($yoyo === null) return 0.00;
        $yoyo = round($yoyo, 1);

        if ($sexo === 'MASCULINO') {
            return match ($yoyo) {
                16.5, 16.6 => 0.5, 16.7, 16.8 => 0.5, 17.1 => 1.5, 17.2 => 2.00,
                17.3 => 2.5, 17.4 => 3.00, 17.5 => 3.5, 17.6 => 4.00, 17.7 => 4.50,
                17.8 => 5.00, 18.1 => 5.50, 18.2 => 6.00, 18.3 => 6.50, 
                18.4 => 7.00, 18.5 => 7.50, 18.6 => 8.00, 18.7 => 8.50,
                18.8 => 9.00, 19.1 => 9.50, 19.2 => 10.00, 
                default => 0.00
            };
        }

        if ($sexo === 'FEMENINO') {
            return match ($yoyo) {
                15.4 => 0.00, 15.5 => 0.50, 15.6 => 1.00, 15.7 => 1.50, 15.8 => 2.00,
                16.1 => 2.50, 16.2 => 3.00, 16.3 => 3.50, 16.4 => 4.00, 16.5 => 4.50,
                16.6 => 5.00, 16.7 => 5.50, 16.8 => 6.00, 17.1 => 7.00, 17.2 => 8.00,
                17.3 => 9.00, 17.4 => 10.00, 
                default => 0.00
            };
        }

        return 0.00;
    }

    private function calcularBonificacionFisico3(?float $yoyo, string $sexo): float
    {
        if ($yoyo === null) return 0.00;
        $yoyo = round($yoyo, 1);

        if ($sexo === 'MASCULINO') {
            return match ($yoyo) {
                17.1, 17.2 => 0.25, 17.3 => 0.5, 17.4 => 0.75, 17.5 => 1.25,
                17.6 => 1.5, 17.7 => 1.75, 17.8 => 2.0, 18.1 => 2.5, 18.2 => 3.0,
                18.3 => 3.5, 18.4 => 4.0, 18.5 => 4.5, 18.6 => 5.0,
                18.7 => 5.5, 18.8 => 6.0, 19.1 => 6.5, 19.2 => 7.0, default => 0.00
            };
        }

        if ($sexo === 'FEMENINO') {
            return match ($yoyo) {
                15.1 => 0.25, 15.2 => 0.50, 15.3 => 0.75,
                15.4 => 1.00, 15.5 => 1.50, 15.6 => 2.00,
                15.7 => 2.50, 15.8 => 3.00, 16.1 => 3.50,
                16.2 => 4.00, 16.3 => 4.50, 16.4 => 5.00,
                16.5 => 5.50, 16.6 => 6.00, 16.7 => 6.50,
                16.8 => 7.00,
                default => 0.00
            };
        }

        return 0.00;
    }

    private function calcularBonificacionFisico3_oficiales(?float $yoyo, string $sexo): float
    {
        if ($yoyo === null) return 0.00;
        $yoyo = round($yoyo, 1);

        if ($sexo === 'MASCULINO') {
            return match ($yoyo) {
                16.4 => 0.00, 16.5, 16.6 => 1.00, 16.7, 16.8 => 2.00, 17.1 => 3.00, 17.2 => 4.00,
                17.3 => 5.00, 17.4 => 6.00, 17.5 => 7.00, 17.6 => 8.00, 17.7 => 9.00, 17.8 => 10.00,
                18.1 => 11.00, 18.2 => 12.00, 18.3 => 13.00, 18.4 => 14.00, 18.5 => 15.00, 18.6 => 16.00, 
                18.7 => 17.00, 18.8 => 18.00, 19.1 => 19.00, 19.2 => 20.00, 
                default => 0.00
            };
        }

        if ($sexo === 'FEMENINO') {
            return match ($yoyo) {
                15.4 => 0.00, 15.5 => 1.00, 15.6 => 2.00, 15.7 => 3.00, 15.8 => 4.00,
                16.1 => 5.00, 16.2 => 6.00, 16.3 => 7.00, 16.4 => 8.00, 16.5 => 9.00,
                16.6 => 10.00, 16.7 => 11.00, 16.8 => 12.00, 17.1 => 14.00, 17.2 => 16.00,
                17.3 => 18.00, 17.4 => 20.00, 
                default => 0.00
            };
        }

        return 0.00;
    }

    private function calcularBonificacionFisico1_auxiliares(?float $yoyo, string $sexo): float
    {
        if ($yoyo === null) return 0.00;
        $yoyo = round($yoyo, 1);

        if ($sexo === 'MASCULINO') {
            return match ($yoyo) {
                15.4 => 0.00, 
                15.5, 15.6, 15.7, 15.8 => 5.00,
                16.1, 16.2, 16.3, 16.4 => 10.00,
                16.5, 16.6, 16.7, 16.8 => 15.00,
                17.1, 17.2, 17.3, 17.4, 17.5 => 20.00,
                17.6, 17.7, 17.8, 18.1, 18.2 => 25.00,
                default => 0.00
            };
        }

        if ($sexo === 'FEMENINO') {
            return match ($yoyo) {
                14.8 => 0.00,
                15.1, 15.2, 15.3 => 5.00,
                15.4, 15.5, 15.6 => 10.00,
                15.7, 15.8, 16.1 => 15.00,
                16.2, 16.3, 16.4, 16.5 => 20.00,
                17.6, 17.7, 17.8, 17.1, 17.2 => 25.00, 
                default => 0.00
            };
        }

        return 0.00;
    }

    private function calcularBonificacionInforme(?float $nota): float
    {
        if ($nota === null) return 0.00;

        return match (true) {
            $nota <= 49 => 0.00,
            $nota <= 53 => 3.75,
            $nota <= 57 => 7.50,
            $nota <= 61 => 11.25,
            $nota <= 65 => 15.00,
            $nota <= 69 => 18.75,
            $nota <= 73 => 22.50,
            $nota <= 77 => 26.25,
            default     => 30.00,
        };
    }

    public function generarExcelProvincial (Categorias $categoria): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Clasificación');

        // 1. Insertar logo si existe
        $logoPath = __DIR__ . '/../../public/assets/logo_rfaf.png';
        if (file_exists($logoPath)) {
            $drawing = new Drawing();
            $drawing->setPath($logoPath);
            $drawing->setHeight(80);
            $drawing->setCoordinates('A1');
            $drawing->setWorksheet($sheet);
        }

        // 2. Título
        /*$sheet->setCellValue('A6', 'CLASIFICACIÓN PROVINCIALES');
        $lastCol = $sheet->getHighestColumn();
        $sheet->mergeCells("A6:{$lastCol}6");
        $sheet->getStyle('A6')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
        */

        // 3. Cabeceras
        $headers = [
            "N", "CÓDIGO", "ÁRBITRO/A",
            "ACIERTOS", "BONIFICACIÓN",
            "YO-YO", "MEDIA VEL.", "BONIFICACIÓN",
            "NOTA", "BONIFICACIÓN",
            "YO-YO", "MEDIA VEL.", "BONIFICACIÓN",
            "NOTA", "BONIFICACIÓN",
            "YO-YO", "MEDIA VEL.", "BONIFICACIÓN",
            "INFORME 1", "INFORME 2", "BONIFICACIÓN",
            "CLASES", "BONIFICACIÓN",
            "ASISTENCIA", "SIMULACROS", "BONIFICACIÓN",
            "SANCIONES", "PUNTOS", "OBSERVACIONES"
        ];

        /*Fila 9 - Cabecera agrupada
        $sheet->mergeCells("A9:A10");
        $sheet->setCellValue("A9", "Nº");

        $sheet->mergeCells("B9:B10");
        $sheet->setCellValue("B9", "CÓDIGO");

        $sheet->mergeCells("C9:C10");
        $sheet->setCellValue("C9", "ÁRBITRO");*/

        $sheet->mergeCells("D9:E9");
        $sheet->setCellValue("D9", "TÉCNICO 1");

        $sheet->mergeCells("F9:H9");
        $sheet->setCellValue("F9", "FÍSICO 1");

        $sheet->mergeCells("I9:J9");
        $sheet->setCellValue("I9", "TÉCNICO 2");

        $sheet->mergeCells("K9:M9");
        $sheet->setCellValue("K9", "FÍSICO 2");

        $sheet->mergeCells("N9:O9");
        $sheet->setCellValue("N9", "TÉCNICO 3");

        $sheet->mergeCells("P9:R9");
        $sheet->setCellValue("P9", "FÍSICO 3");

        $sheet->mergeCells("S9:U9");
        $sheet->setCellValue("S9", "INFORMES");

        $sheet->mergeCells("V9:W9");
        $sheet->setCellValue("V9", "CLASES");

        $sheet->mergeCells("X9:Z9");
        $sheet->setCellValue("X9", "CARTUJA");

        foreach (['D9', 'F9', 'J9', 'K9', 'N9', 'P9', 'U9', 'V9', 'X9'] as $cell) {
            $sheet->getStyle($cell)->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'font' => ['bold' => true],
            ]);
        }

        // Estilos para filas 9 y 10
        $sheet->getRowDimension(9)->setRowHeight(13.00);
        $sheet->getRowDimension(10)->setRowHeight(135.24);

        $startRow = 10;
        $sheet->fromArray($headers, null, "A{$startRow}");

        // Estilo general para la fila 10 (alineación centrada sin rotación)
        $sheet->getStyle("A10:AC10")->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => false,
                'textRotation' => 255 // apilado verticalmente
            ],
            'font' => [
                'bold' => true,
                'size' => 7
            ]
            /*,
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['argb' => 'FF000000']
                ]
            ]*/
        ]);

        // Estilo general para la fila 9 
        $sheet->getStyle("D9:Z9")->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => false,
            ],
            'font' => [
                'bold' => true,
                'size' => 8
            ]
        ]);

        // Aplicar estilos por bloque de columnas ffcccc
        $colores = [
            'A' => 'FFFFFF', 'B' => 'FFFFFF', 'C' => 'FFFFFF',
            'D' => 'E2EFDA', 'E' => 'E2EFDA',
            'F' => 'DDEBF7', 'G' => 'DDEBF7', 'H' => 'DDEBF7',
            'I' => 'FFF2CC', 'J' => 'FFF2CC',
            'K' => 'E6E1F9', 'L' => 'E6E1F9', 'M' => 'E6E1F9',
            'N' => 'FCE4D6', 'O' => 'FCE4D6',
            'P' => 'D9E1F2', 'Q' => 'D9E1F2', 'R' => 'D9E1F2',
            'S' => 'FFFFCC', 'T' => 'FFFFCC', 'U' => 'FFFFCC',
            'V' => 'CCFFCC', 'W' => 'CCFFCC',
            'X' => 'FFCCCC', 'Y' => 'FFCCCC', 'Z' => 'FFCCCC',
            'AA' => 'FFFFFF', 'AB' => 'FFFFFF', 'AC' => 'FFFFFF'
        ];

        foreach ($colores as $col => $color) {
            $sheet->getStyle("{$col}9")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($color);
        }

        foreach ($colores as $col => $color) {
            $sheet->getStyle("{$col}10")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($color);
        }

        $arbitros = $this->arbRepo->findBy(['categoria' => $categoria]);
        $datos = [];

        foreach ($arbitros as $arb) {
            $nif     = $arb->getNif();
            $codigo  = strtoupper(substr($nif, -4));
            $nombre  = sprintf('%s, %s', $arb->getFirstSurname() . ' ' . $arb->getSecondSurname(), $arb->getName());
            $sexo    = strtoupper($arb->getSexo());

            // Técnico 1
            $tec1 = $this->tecnicosRepo->createQueryBuilder('t')
                ->join('t.session', 's')
                ->where('t.arbitro = :arb')->andWhere('s.examNumber = 1')
                ->setParameter('arb', $arb)->setMaxResults(1)
                ->getQuery()->getOneOrNullResult();
            $tec1Nota = $tec1?->getNota();
            $tec1Bon  = ($tec1 && !$tec1->isRepesca()) ? round($tec1Nota * 0.1, 2) : 0.00;

            // Físico 1
            $fis1 = $this->fisicaRepo->findOneBy(['arbitro' => $arb, 'convocatoria' => 1]);
            $yoyo1 = $fis1?->getYoyo();
            $vel1  = $fis1?->getVelocidad();
            $bonoFis1 = 0.00;
            if ($yoyo1 !== null && $vel1 !== null) {
                $bonoFis1 = match($sexo) {
                    'MASCULINO' => ($yoyo1 >= 16.8 && $vel1 <= 6.2) ? 3.00 : 0.00,
                    'FEMENINO'  => ($yoyo1 >= 14.8 && $vel1 <= 6.6) ? 3.00 : 0.00,
                    default     => 0.00,
                };
            }

            // Técnico 2
            $tec2 = $this->tecnicosRepo->createQueryBuilder('t')
                ->join('t.session', 's')
                ->where('t.arbitro = :arb')->andWhere('s.examNumber = 2')
                ->setParameter('arb', $arb)->setMaxResults(1)
                ->getQuery()->getOneOrNullResult();
            $tec2Nota = $tec2?->getNota();
            $tec2Bon  = ($tec2 && !$tec2->isRepesca()) ? round($tec2Nota * 0.1, 2) : 0.00;

            // Físico 2
            $fis2 = $this->fisicaRepo->findOneBy(['arbitro' => $arb, 'convocatoria' => 2]);
            $yoyo2 = $fis2?->getYoyo();
            $vel2  = $fis2?->getVelocidad();
            $bonoFis2 = $this->calcularBonificacionFisico2($yoyo2, $sexo);

            // Técnico 3
            $tec3 = $this->tecnicosRepo->createQueryBuilder('t')
                ->join('t.session', 's')
                ->where('t.arbitro = :arb')->andWhere('s.examNumber = 3')
                ->setParameter('arb', $arb)->setMaxResults(1)
                ->getQuery()->getOneOrNullResult();
            $tec3Nota = $tec3?->getNota();
            $tec3Bon  = ($tec3 && !$tec3->isRepesca()) ? round($tec3Nota * 0.1, 2) : 0.00;

            // Físico 3
            $fis3 = $this->fisicaRepo->findOneBy(['arbitro' => $arb, 'convocatoria' => 3]);
            $yoyo3 = $fis3?->getYoyo();
            $vel3  = $fis3?->getVelocidad();
            $bonoFis3 = $this->calcularBonificacionFisico3($yoyo3, $sexo);

            // Informes
            $informes = $this->informesRepo->findBy(['arbitro' => $arb], ['fecha' => 'ASC']);
            $nota1 = null;
            $nota2 = null;
            $bonoInforme = 0.00;
            if (count($informes) === 1) {
                $nota1 = $informes[0]->getNota();
                $nota2 = '-';
                $bonoInforme = $this->calcularBonificacionInforme($nota1);
            } elseif (count($informes) >= 2) {
                $nota1 = $informes[0]->getNota();
                $nota2 = $informes[1]->getNota();
                $media = ($nota1 + $nota2) / 2;
                $bonoInforme = $this->calcularBonificacionInforme($media);
            }

            // Clases
            $numClases = $this->asistRepo->createQueryBuilder('a')
                ->select('COUNT(a.id)')
                ->join('a.sesion', 's')
                ->where('a.arbitro = :arb')
                ->andWhere('a.asiste = true')
                ->andWhere('s.categoria = :cat')
                ->setParameter('arb', $arb)
                ->setParameter('cat', $categoria)
                ->getQuery()
                ->getSingleScalarResult();
            $bonoClases = round($numClases * 0.33, 2);

            // Cartuja
            $entreno = $this->entrenosRepo->findOneBy(['arbitro' => $arb]);
            $asistCartuja = 0;
            if ($entreno) {
                $asistCartuja = $entreno->getSeptiembre() + $entreno->getOctubre() + $entreno->getNoviembre()
                              + $entreno->getDiciembre() + $entreno->getEnero() + $entreno->getFebrero()
                              + $entreno->getMarzo() + $entreno->getAbril();
            }

            $simus = $this->simusRepo->findBy(['arbitro' => $arb]);
            $simuPts = 0.00;
            foreach ($simus as $s) {
                $p = $s->getPeriodo();
                $simuPts += match(true) {
                    $sexo === 'MASCULINO' && $p >= 19.2 => 1.00,
                    $sexo === 'MASCULINO' && $p >= 18.2 => 0.50,
                    $sexo === 'FEMENINO' && $p >= 15.4 => 1.00,
                    $sexo === 'FEMENINO' && $p >= 14.8 => 0.50,
                    default => 0.00
                };
            }

            $bonoEntrenos = $asistCartuja > 20 ? 5.00 : round($asistCartuja * 0.25, 2);
            $bonoCartuja = round($bonoEntrenos + $simuPts, 2);

            // Sanciones
            $sancion = $this->sancionesRepo->findOneBy(['arbitro' => $arb]);
            $notaSancion = $sancion?->getNota();

            // Puntos
            $totalPuntos = round(
                $tec1Bon + $bonoFis1 +
                $tec2Bon + $bonoFis2 +
                $tec3Bon + $bonoFis3 +
                $bonoInforme + $bonoClases + $bonoCartuja
                - $notaSancion, 2
            );

            // OBSERVACIONES
            $observaciones = [];

            // 1) FÍSICA 2 y 3: repesca true o yoyo < mínimo
            foreach ([2, 3] as $conv) {
                $f = $this->fisicaRepo->findOneBy(['arbitro' => $arb, 'convocatoria' => $conv]);
                if ($f) {
                    $yoyo = $f->getYoyo();
                    $repesca = $f->getRepesca(); // 👈 asegúrate de que este getter existe
                    $minYoyo = $sexo === 'MASCULINO' ? 16.8 : 14.8;
                    if ($repesca === true || $yoyo < $minYoyo) {
                        $observaciones[] = '(3)';
                        break;
                    }
                }
            }

            // 2) TECNICOS 2 y 3 repesca true
            foreach ([2, 3] as $conv) {
                $tec = $this->tecnicosRepo->createQueryBuilder('t')
                    ->join('t.session', 's')
                    ->where('t.arbitro = :arb')
                    ->andWhere('s.examNumber = :num')
                    ->setParameter('arb', $arb)
                    ->setParameter('num', $conv)
                    ->setMaxResults(1)
                    ->getQuery()->getOneOrNullResult();
                if ($tec && $tec->isRepesca() === true) {
                    $observaciones[] = '(1)';
                    break;
                }
            }

            // 3) DOS TECNICOS o DOS FISICAS SEGUIDAS no presentados
            $faltasTec = 0;
            $faltasFis = 0;
            foreach ([1, 2, 3] as $conv) {
                // tecnicos
                $tec = $this->tecnicosRepo->createQueryBuilder('t')
                    ->join('t.session', 's')
                    ->where('t.arbitro = :arb')
                    ->andWhere('s.examNumber = :num')
                    ->setParameter('arb', $arb)
                    ->setParameter('num', $conv)
                    ->setMaxResults(1)
                    ->getQuery()->getOneOrNullResult();
                if ($tec && $tec->getNota() == 0 && $tec->isRepesca()) $faltasTec++;

                // fisicas
                $f = $this->fisicaRepo->findOneBy(['arbitro' => $arb, 'convocatoria' => $conv]);
                if ($f && $f->getYoyo() == 0 && $f->getVelocidad() == 0) $faltasFis++;
            }

            if ($faltasTec >= 2 || $faltasFis >= 2) {
                $observaciones[] = '(5)';
            }

            $datos[] = [
                'codigo' => $codigo,
                'nombre' => $nombre,
                'fila' => [
                    0, // placeholder del Nº, lo pondremos después
                    $codigo, $nombre,
                    $tec1Nota, $tec1Bon,
                    $yoyo1, $vel1, $bonoFis1,
                    $tec2Nota, $tec2Bon,
                    $yoyo2, $vel2, $bonoFis2,
                    $tec3Nota, $tec3Bon,
                    $yoyo3, $vel3, $bonoFis3,
                    $nota1, $nota2, $bonoInforme,
                    $numClases, $bonoClases,
                    $asistCartuja, $simuPts, $bonoCartuja,
                    $notaSancion, $totalPuntos, implode(' ', $observaciones)
                ],
                'puntos' => $totalPuntos
            ];
        }

        // Ordenamos los datos por 'puntos' de mayor a menor
        usort($datos, fn($a, $b) => $b['puntos'] <=> $a['puntos']);

        $rowIndex = $startRow + 1;
        $n = 1;

        foreach ($datos as $dato) {
            $fila = $dato['fila'];
            $fila[0] = $n++; // Colocamos el número de orden (Nº)
            $sheet->fromArray($fila, null, "A{$rowIndex}");

            foreach ([
                'E'  => '0.00', // Bonificación T1
                'F'  => '0.0',  // Yoyo F1
                'G'  => '0.00', // Velocidad F1
                'H'  => '0.00', // Bono F1
                'I'  => '0.00', // Nota T2
                'J'  => '0.00', // Bono T2
                'K'  => '0.0',  // Yoyo F2
                'L'  => '0.00', // Vel F2
                'M'  => '0.00', // Bono F2
                'N'  => '0.00', // Nota T3
                'O'  => '0.00', // Bono T3
                'P'  => '0.0',  // Yoyo F3
                'Q'  => '0.00', // Vel F3
                'R'  => '0.00', // Bono F3
                'S'  => '0.00', // Informe 1
                'T'  => '0.00', // Informe 2
                'U'  => '0.00', // Bono Informes
                'W'  => '0.00', // Bono Clases
                'Y'  => '0.00', // Asistencia Cartuja
                'Z'  => '0.00', // Simulacros Cartuja
                'AA' => '0.00', // Bono Cartuja
                'AB' => '0.00'  // Sanciones
            ] as $col => $format) {
                $sheet->getStyle("{$col}{$rowIndex}")
                    ->getNumberFormat()
                    ->setFormatCode($format);
            }

            // Tamaño de letra 9 pt en cada fila desde la 11
           $sheet->getStyle("A{$rowIndex}:AC{$rowIndex}")
            ->getFont()
            ->setSize(9);
                
            $rowIndex++;
        }

        $lastRow = $sheet->getHighestRow();

        $bloques = [
            'A9:A'   . $lastRow,
            'B9:B'   . $lastRow,
            'C9:C'   . $lastRow,
            'D9:E'   . $lastRow,
            'F9:H'   . $lastRow,
            'I9:J'   . $lastRow,
            'K9:M'   . $lastRow,
            'N9:O'   . $lastRow,
            'P9:R'   . $lastRow,
            'S9:U'   . $lastRow,
            'V9:W'   . $lastRow,
            'X9:Z'   . $lastRow,
            'AA9:AA' . $lastRow,
            'AB9:AB' . $lastRow,
            'AC9:AC' . $lastRow,
        ];

        foreach ($bloques as $rango) {
            $sheet->getStyle($rango)->applyFromArray([
                'borders' => [
                    'outline' => [
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color' => ['argb' => 'FF000000'],
                    ],
                ],
            ]);
        }

        $columnasDiscontinuas = [
            'D', 'F', 'G', 'I', 'K', 'L', 'N', 'P', 'Q', 'S', 'T', 'V', 'X', 'Y'
        ];

        foreach ($columnasDiscontinuas as $col) {
            $sheet->getStyle("{$col}9:{$col}{$lastRow}")->applyFromArray([
                'borders' => [
                    'right' => [
                        'borderStyle' => Border::BORDER_DASHED,
                        'color' => ['argb' => 'FF000000'],
                    ],
                ],
            ]);
        }

        $sheet->getStyle("A10:AC10")->applyFromArray([
            'borders' => [
                'bottom' => [
                    'borderStyle' => Border::BORDER_HAIR,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        for ($fila = 11; $fila <= $lastRow; $fila++) {
            if ($fila % 2 !== 0) { // Solo filas impares
                $sheet->getStyle("A{$fila}:AC{$fila}")->getFill()->setFillType(Fill::FILL_SOLID);
                $sheet->getStyle("A{$fila}:AC{$fila}")->getFill()->getStartColor()->setRGB('F5F5F5');
            }
        }

        $lastCol = $sheet->getHighestColumn();

        $sheet->getStyle("A10:{$lastCol}{$lastRow}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        // Ahora que se han escrito los datos: aplicar bordes y autosize
        $lastRow = $sheet->getHighestRow();
        $lastCol = $sheet->getHighestColumn();

        /*$sheet->getStyle("A{$startRow}:{$lastCol}{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000']
                ]
            ]
        ]);*/

        $columns = [];
        $col = 'A';
        while ($col !== 'AD') { // 'AD' es la columna siguiente a 'AC'
            $columns[] = $col;
            $col++;
        }

        foreach ($columns as $col) {
            switch ($col) {
                case 'C':
                    $sheet->getColumnDimension($col)->setAutoSize(false);
                    $sheet->getColumnDimension($col)->setWidth(27.64); // ≈ 7.49 cm
                    break;
                case in_array($col, ['B', 'AB']):
                    $sheet->getColumnDimension($col)->setAutoSize(false);
                    $sheet->getColumnDimension($col)->setWidth(5.6); // ≈ 1.52 cm
                    break;
                default:
                    $sheet->getColumnDimension($col)->setAutoSize(false);
                    $sheet->getColumnDimension($col)->setWidth(4.97); // ≈ 1.32 cm
                    break;
            }
        }

        $filename = 'clasificacion_' . strtolower($categoria->getName()) . '.xlsx';
        $filePath = sys_get_temp_dir() . '/' . $filename;

        // Leyenda Observaciones
        $sheet->mergeCells('Y1:AC1');
        $sheet->setCellValue('Y1', 'LEYENDA OBSERVACIONES');
        $sheet->getStyle('Y1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 9],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->mergeCells('Y2:AC2');
        $sheet->setCellValue('Y2', 'NO APTO ASCENSO');
        $sheet->getStyle('Y2')->applyFromArray([
            'font' => ['bold' => true, 'italic' => true, 'size' => 8],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $leyenda = [
            ' (1) Técnico',
            ' (2) No actúa',
            ' (3) Pruebas físicas',
            ' (4) Circular 3 (edad)',
            ' (5) Descenso art. 30 Libro II',
        ];

        $row = 3;
        foreach ($leyenda as $texto) {
            $sheet->mergeCells("Y{$row}:AC{$row}");
            $sheet->setCellValue("Y{$row}", $texto); 
            $sheet->getStyle("Y{$row}")->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                'font' => ['size' => 7],
            ]);
            $row++;
        }

        // Borde al cuadro completo
        $sheet->getStyle('Y1:AC7')->applyFromArray([
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_HAIR,
                    'color' => ['argb' => 'FF000000']
                ]
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F9F9F9']
            ]
        ]);

        (new Xlsx($spreadsheet))->save($filePath);
        return $filePath;
    }

    public function generarExcelOficial (Categorias $categoria): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Clasificación');

        $headers = [
            "Nº", "CÓDIGO", "ÁRBITRO/A",
            "TÉCNICO 1\nACIERTOS", "TÉCNICO 1\nBONIFICACIÓN",
            "FÍSICO 1\nYO-YO", "FÍSICO 1\nBONIFICACIÓN",
            "TÉCNICO 2\nNOTA", "TÉCNICO 2\nBONIFICACIÓN",
            "FÍSICO 2\nYO-YO", "FÍSICO 2\nBONIFICACIÓN",
            "TÉCNICO 3\nNOTA", "TÉCNICO 3\nBONIFICACIÓN",
            "FÍSICO 3\nYO-YO", "FÍSICO 3\nVEL.", "FÍSICO 3\nBONIFICACIÓN",
            "CLASES", "CLASES\nBONIFICACIÓN",
            "CARTUJA\nASISTENCIA", "CARTUJA\nSIMULACROS", "CARTUJA\nBONIFICACIÓN",
            "TEST ONLINE", "SANCIONES", "PUNTOS", "OBSERVACIONES"
        ];

        $sheet->fromArray($headers, null, 'A1');

        $arbitros = $this->arbRepo->findBy(['categoria' => $categoria]);
        
        $datos = [];

        foreach ($arbitros as $arb) {
            $nif     = $arb->getNif();
            $codigo  = strtoupper(substr($nif, -4));
            $nombre  = sprintf('%s, %s', $arb->getFirstSurname(), $arb->getName());
            $sexo    = strtoupper($arb->getSexo());

            // Técnico 1
            $tec1 = $this->tecnicosRepo->createQueryBuilder('t')
                ->join('t.session', 's')
                ->where('t.arbitro = :arb')->andWhere('s.examNumber = 1')
                ->setParameter('arb', $arb)->setMaxResults(1)
                ->getQuery()->getOneOrNullResult();
            $tec1Nota = $tec1?->getNota();
            $tec1Bon  = ($tec1 && !$tec1->isRepesca()) ? round($tec1Nota * 0.25, 2) : 0.00;

            // Físico 1
            $fis1 = $this->fisicaRepo->findOneBy(['arbitro' => $arb, 'convocatoria' => 1]);
            $yoyo1 = $fis1?->getYoyo();
            $bonoFis1 = 0.00;
            if ($yoyo1 !== null) {
                $bonoFis1 = match($sexo) {
                    'MASCULINO' => ($yoyo1 >= 15.4) ? 5.00 : 0.00,
                    'FEMENINO'  => ($yoyo1 >= 14.8) ? 5.00 : 0.00,
                    default     => 0.00,
                };
            }

            // Técnico 2
            $tec2 = $this->tecnicosRepo->createQueryBuilder('t')
                ->join('t.session', 's')
                ->where('t.arbitro = :arb')->andWhere('s.examNumber = 2')
                ->setParameter('arb', $arb)->setMaxResults(1)
                ->getQuery()->getOneOrNullResult();
            $tec2Nota = $tec2?->getNota();
            $tec2Bon  = ($tec2 && !$tec2->isRepesca()) ? round($tec2Nota * 0.1, 2) : 0.00;

            // Físico 2
            $fis2 = $this->fisicaRepo->findOneBy(['arbitro' => $arb, 'convocatoria' => 2]);
            $yoyo2 = $fis2?->getYoyo();
            $bonoFis2 = $this->calcularBonificacionFisico2_oficiales($yoyo2, $sexo);

            // Técnico 3
            $tec3 = $this->tecnicosRepo->createQueryBuilder('t')
                ->join('t.session', 's')
                ->where('t.arbitro = :arb')->andWhere('s.examNumber = 3')
                ->setParameter('arb', $arb)->setMaxResults(1)
                ->getQuery()->getOneOrNullResult();
            $tec3Nota = $tec3?->getNota();
            $tec3Bon  = ($tec3 && !$tec3->isRepesca()) ? round($tec3Nota * 1, 2) : 0.00;

            // Físico 3
            $fis3 = $this->fisicaRepo->findOneBy(['arbitro' => $arb, 'convocatoria' => 3]);
            $yoyo3 = $fis3?->getYoyo();
            $vel3  = $fis3?->getVelocidad();
            $bonoFis3 = $this->calcularBonificacionFisico3_oficiales($yoyo3, $sexo);

            // Clases
            $numClases = $this->asistRepo->createQueryBuilder('a')
                ->select('COUNT(a.id)')
                ->join('a.sesion', 's')
                ->where('a.arbitro = :arb')
                ->andWhere('a.asiste = true')
                ->andWhere('s.categoria = :cat')
                ->setParameter('arb', $arb)
                ->setParameter('cat', $categoria)
                ->getQuery()
                ->getSingleScalarResult();
            $bonoClases = round($numClases * 1, 2);

            // Cartuja
            $entreno = $this->entrenosRepo->findOneBy(['arbitro' => $arb]);
            $asistCartuja = 0;
            if ($entreno) {
                $asistCartuja = $entreno->getSeptiembre() + $entreno->getOctubre() + $entreno->getNoviembre()
                              + $entreno->getDiciembre() + $entreno->getEnero() + $entreno->getFebrero()
                              + $entreno->getMarzo() + $entreno->getAbril();
            }

            $simus = $this->simusRepo->findBy(['arbitro' => $arb]);
            $simuPts = 0.00;
            foreach ($simus as $s) {
                $p = $s->getPeriodo();
                $simuPts += match(true) {
                    $sexo === 'MASCULINO' && $p >= 19.2 => 1.00,
                    $sexo === 'MASCULINO' && $p >= 18.2 => 0.50,
                    $sexo === 'FEMENINO' && $p >= 15.4 => 1.00,
                    $sexo === 'FEMENINO' && $p >= 14.8 => 0.50,
                    default => 0.00
                };
            }

            $bonoEntrenos = $asistCartuja > 20 ? 5.00 : round($asistCartuja * 0.20, 2);
            $bonoCartuja = round($bonoEntrenos + $simuPts, 2);

            // Test Online
            $tests = $this->testRepo->findBy(['arbitro' => $arb]);
            $totalNotaTest = array_sum(array_map(fn($t) => $t->getNota(), $tests));
            $bonoTestOnline = round($totalNotaTest * 0.0667, 2);

            // Sanciones
            $sancion = $this->sancionesRepo->findOneBy(['arbitro' => $arb]);
            $notaSancion = $sancion?->getNota();

            // Puntos
            $totalPuntos = round(
                $tec1Bon + $bonoFis1 +
                $tec2Bon + $bonoFis2 +
                $tec3Bon + $bonoFis3 +
                $bonoClases + $bonoCartuja + $bonoTestOnline
                - $notaSancion, 2
            );

            // OBSERVACIONES
            $observaciones = [];

            // 1) FÍSICA 2 y 3: repesca true o yoyo < mínimo
            foreach ([2, 3] as $conv) {
                $f = $this->fisicaRepo->findOneBy(['arbitro' => $arb, 'convocatoria' => $conv]);
                if ($f) {
                    $yoyo = $f->getYoyo();
                    $repesca = $f->getRepesca(); 
                    $minYoyo = $sexo === 'MASCULINO' ? 15.8 : 14.8;
                    if ($repesca === true || $yoyo < $minYoyo) {
                        $observaciones[] = '(3)';
                        break;
                    }
                }
            }

            // 2) TECNICOS 2 y 3 repesca true
            foreach ([2, 3] as $conv) {
                $tec = $this->tecnicosRepo->createQueryBuilder('t')
                    ->join('t.session', 's')
                    ->where('t.arbitro = :arb')
                    ->andWhere('s.examNumber = :num')
                    ->setParameter('arb', $arb)
                    ->setParameter('num', $conv)
                    ->setMaxResults(1)
                    ->getQuery()->getOneOrNullResult();
                if ($tec && $tec->isRepesca() === true) {
                    $observaciones[] = '(1)';
                    break;
                }
            }

            $datos[] = [
                'codigo' => $codigo,
                'nombre' => $nombre,
                'fila' => [
                    0, 
                    $codigo, $nombre,
                    $tec1Nota, $tec1Bon,
                    $yoyo1, $bonoFis1,
                    $tec2Nota, $tec2Bon,
                    $yoyo2, $bonoFis2,
                    $tec3Nota, $tec3Bon,
                    $yoyo3, $vel3, $bonoFis3,
                    $numClases, $bonoClases,
                    $asistCartuja, $simuPts, $bonoCartuja,
                    $bonoTestOnline, $notaSancion, $totalPuntos, implode(' ', $observaciones)
                ],
                'puntos' => $totalPuntos
            ];
        }

        // Ordenamos los datos por 'puntos' de mayor a menor
        usort($datos, fn($a, $b) => $b['puntos'] <=> $a['puntos']);

        $rowIndex = 2;
        $n = 1;

        foreach ($datos as $dato) {
            $fila = $dato['fila'];
            $fila[0] = $n++; // Colocamos el número de orden (Nº)
            $sheet->fromArray($fila, null, "A{$rowIndex}");
            $rowIndex++;
        }

        $lastColumn = $sheet->getHighestColumn();
        foreach (range('A', $lastColumn) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'clasificacion_' . strtolower($categoria->getName()) . '.xlsx';
        $filePath = sys_get_temp_dir() . '/' . $filename;

        (new Xlsx($spreadsheet))->save($filePath);
        return $filePath;
    }

    public function generarExcelAuxiliar (Categorias $categoria): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Clasificación');

        $headers = [
            "Nº", "CÓDIGO", "ÁRBITRO/A",
            "TÉCNICO 1\nACIERTOS", "TÉCNICO 1\nBONIFICACIÓN",
            "TÉCNICO 2\nNOTA", "TÉCNICO 2\nBONIFICACIÓN",
            "TÉCNICO 3\nNOTA", "TÉCNICO 3\nBONIFICACIÓN",
            "FÍSICO 1\nYO-YO", "FÍSICO 1\nBONIFICACIÓN",
            "CLASES", "CLASES\nBONIFICACIÓN",
            "TEST ONLINE", "SANCIONES", "PUNTOS", "OBSERVACIONES"
        ];

        $sheet->fromArray($headers, null, 'A1');

        $arbitros = $this->arbRepo->findBy(['categoria' => $categoria]);
        
        $datos = [];

        foreach ($arbitros as $arb) {
            $nif     = $arb->getNif();
            $codigo  = strtoupper(substr($nif, -4));
            $nombre  = sprintf('%s, %s', $arb->getFirstSurname(), $arb->getName());
            $sexo    = strtoupper($arb->getSexo());

            // Técnico 1
            $tec1 = $this->tecnicosRepo->createQueryBuilder('t')
                ->join('t.session', 's')
                ->where('t.arbitro = :arb')->andWhere('s.examNumber = 1')
                ->setParameter('arb', $arb)->setMaxResults(1)
                ->getQuery()->getOneOrNullResult();
            $tec1Nota = $tec1?->getNota();
            $tec1Bon  = ($tec1 && !$tec1->isRepesca()) ? round($tec1Nota * 0.25, 2) : 0.00;

            // Técnico 2
            $tec2 = $this->tecnicosRepo->createQueryBuilder('t')
                ->join('t.session', 's')
                ->where('t.arbitro = :arb')->andWhere('s.examNumber = 2')
                ->setParameter('arb', $arb)->setMaxResults(1)
                ->getQuery()->getOneOrNullResult();
            $tec2Nota = $tec2?->getNota();
            $tec2Bon  = ($tec2 && !$tec2->isRepesca()) ? round($tec2Nota * 0.1, 2) : 0.00;

            // Técnico 3
            $tec3 = $this->tecnicosRepo->createQueryBuilder('t')
                ->join('t.session', 's')
                ->where('t.arbitro = :arb')->andWhere('s.examNumber = 3')
                ->setParameter('arb', $arb)->setMaxResults(1)
                ->getQuery()->getOneOrNullResult();
            $tec3Nota = $tec3?->getNota();
            $tec3Bon  = ($tec3 && !$tec3->isRepesca()) ? round($tec3Nota * 1, 2) : 0.00;

            // Físico 1
            $fis3 = $this->fisicaRepo->findOneBy(['arbitro' => $arb, 'convocatoria' => 1]);
            $yoyo3 = $fis3?->getYoyo();
            $bonoFis1 = $this->calcularBonificacionFisico3_oficiales($yoyo3, $sexo);

            // Clases
            $numClases = $this->asistRepo->createQueryBuilder('a')
                ->select('COUNT(a.id)')
                ->join('a.sesion', 's')
                ->where('a.arbitro = :arb')
                ->andWhere('a.asiste = true')
                ->andWhere('s.categoria = :cat')
                ->setParameter('arb', $arb)
                ->setParameter('cat', $categoria)
                ->getQuery()
                ->getSingleScalarResult();
            $bonoClases = round($numClases * 0,834, 2);

            // Test Online
            $tests = $this->testRepo->findBy(['arbitro' => $arb]);
            $totalNotaTest = array_sum(array_map(fn($t) => $t->getNota(), $tests));
            $bonoTestOnline = round($totalNotaTest * 0.1, 2);

            // Sanciones
            $sancion = $this->sancionesRepo->findOneBy(['arbitro' => $arb]);
            $notaSancion = $sancion?->getNota();

            // Puntos
            $totalPuntos = round(
                $tec1Bon + $tec2Bon +
                $tec3Bon + $bonoFis1 +
                $bonoClases + $bonoTestOnline
                - $notaSancion, 2
            );

            // OBSERVACIONES
            $observaciones = [];

            // 1) FÍSICA 2 y 3: repesca true o yoyo < mínimo
            foreach ([1] as $conv) {
                $f = $this->fisicaRepo->findOneBy(['arbitro' => $arb, 'convocatoria' => $conv]);
                if ($f) {
                    $yoyo = $f->getYoyo();
                    $repesca = $f->getRepesca(); 
                    $minYoyo = $sexo === 'MASCULINO' ? 15.4 : 14.8;
                    if ($repesca === true || $yoyo < $minYoyo) {
                        $observaciones[] = '(4)';
                        break;
                    }
                }
            }

            // 2) TECNICOS 2 y 3 repesca true
            foreach ([2, 3] as $conv) {
                $tec = $this->tecnicosRepo->createQueryBuilder('t')
                    ->join('t.session', 's')
                    ->where('t.arbitro = :arb')
                    ->andWhere('s.examNumber = :num')
                    ->setParameter('arb', $arb)
                    ->setParameter('num', $conv)
                    ->setMaxResults(1)
                    ->getQuery()->getOneOrNullResult();
                if ($tec && $tec->isRepesca() === true) {
                    $observaciones[] = '(3)';
                    break;
                }
            }

            $datos[] = [
                'codigo' => $codigo,
                'nombre' => $nombre,
                'fila' => [
                    0, 
                    $codigo, $nombre,
                    $tec1Nota, $tec1Bon,
                    $tec2Nota, $tec2Bon,
                    $tec3Nota, $tec3Bon,
                    $yoyo3,$bonoFis1,
                    $numClases, $bonoClases,
                    $bonoTestOnline, $notaSancion, $totalPuntos, implode(' ', $observaciones)
                ],
                'puntos' => $totalPuntos
            ];
        }

        // Ordenamos los datos por 'puntos' de mayor a menor
        usort($datos, fn($a, $b) => $b['puntos'] <=> $a['puntos']);

        $rowIndex = 2;
        $n = 1;

        foreach ($datos as $dato) {
            $fila = $dato['fila'];
            $fila[0] = $n++; // Colocamos el número de orden (Nº)
            $sheet->fromArray($fila, null, "A{$rowIndex}");
            $rowIndex++;
        }

        $lastColumn = $sheet->getHighestColumn();
        foreach (range('A', $lastColumn) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'clasificacion_' . strtolower($categoria->getName()) . '.xlsx';
        $filePath = sys_get_temp_dir() . '/' . $filename;

        (new Xlsx($spreadsheet))->save($filePath);
        return $filePath;
    }
}
