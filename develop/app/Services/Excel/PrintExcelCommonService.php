<?php

namespace App\Services\Excel;

use App\Helpers\LogHelper;
use PhpOffice\PhpSpreadsheet\Style\Border;

class PrintExcelCommonService
{
    /** 罫線設定 */
    // 罫線：外->細線／内->細線(NAVY)
    public static $arrStyleThin = [
        'borders' => [
            'top' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => '000000'],
            ],
            'right' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => '000000'],
            ],
            'left' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => '000000'],
            ],
            'bottom' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => '000000'],
            ],
        ],
    ];

    public static $arrStyleDottedThinDottedThin = [
        'borders' => [
            'top' => [
                'borderStyle' => Border::BORDER_DOTTED,
                'color' => ['rgb' => '000000'],
            ],
            'right' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => '000000'],
            ],
            'left' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => '000000'],
            ],
            'bottom' => [
                'borderStyle' => Border::BORDER_DOTTED,
                'color' => ['rgb' => '000000'],
            ],
        ],
    ];

    public static $arrStyleDottedThikDottedThin = [
        'borders' => [
            'top' => [
                'borderStyle' => Border::BORDER_DOTTED,
                'color' => ['rgb' => '000000'],
            ],
            'right' => [
                'borderStyle' => Border::BORDER_MEDIUM,
                'color' => ['rgb' => '000000'],
            ],
            'left' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => '000000'],
            ],
            'bottom' => [
                'borderStyle' => Border::BORDER_DOTTED,
                'color' => ['rgb' => '000000'],
            ],
        ],
    ];

    public static $arrStyleDottedThikDottedMEDIUM = [
        'borders' => [
            'top' => [
                'borderStyle' => Border::BORDER_DOTTED,
                'color' => ['rgb' => '000000'],
            ],
            'right' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => '000000'],
            ],
            'left' => [
                'borderStyle' => Border::BORDER_MEDIUM,
                'color' => ['rgb' => '000000'],
            ],
            'bottom' => [
                'borderStyle' => Border::BORDER_DOTTED,
                'color' => ['rgb' => '000000'],
            ],
        ],
    ];

    public static $arrStyleBORDER_THICK = [
        'borders' => [
            'top' => [
                'borderStyle' => Border::BORDER_MEDIUM,
                'color' => ['rgb' => '000000'],
            ],
        ],
    ];

    // 罫線：外->細線／内->細線(BLACK)
    public static $arrStyleAllThinBlack = [
        'borders' => [
            'outline' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => '000000'],
            ],
            'inside' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => '000000'],
            ],
        ],
    ];

    // 罫線：外->太線／内->微細線
    public static $arrStyleThinInsideHair = [
        'borders' => [
            'outline' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => '000000'],
            ],
            'inside' => [
                'borderStyle' => Border::BORDER_HAIR,
                'color' => ['rgb' => '000000'],
            ],
        ],
    ];

    // 罫線：外->細線／内->微細線
    public static $arrStyleMediumInsideHair = [
        'borders' => [
            'outline' => [
                'borderStyle' => Border::BORDER_MEDIUM,
                'color' => ['rgb' => '000000'],
            ],
            'inside' => [
                'borderStyle' => Border::BORDER_HAIR,
                'color' => ['rgb' => '000000'],
            ],
        ],
    ];

    // 罫線：下・左・右->細線／上・内->微細線
    public static $arrStyleTableBottom = [
        'borders' => [
            'top' => [
                'borderStyle' => Border::BORDER_HAIR,
                'color' => ['rgb' => '000000'],
            ],
            'right' => [
                'borderStyle' => Border::BORDER_MEDIUM,
                'color' => ['rgb' => '000000'],
            ],
            'left' => [
                'borderStyle' => Border::BORDER_MEDIUM,
                'color' => ['rgb' => '000000'],
            ],
            'bottom' => [
                'borderStyle' => Border::BORDER_MEDIUM,
                'color' => ['rgb' => '000000'],
            ],
        ],
    ];

    // 罫線：下->細線
    public static $arrStyleBeyondTable = [
        'borders' => [
            'bottom' => [
                'borderStyle' => Border::BORDER_MEDIUM,
                'color' => ['rgb' => '000000'],
            ],
        ],
    ];

    /**
     * ■罫線：全て->微細線
     *
     * @var \array[][]
     */
    public static $arrStyleAllHair = [
        'borders' => [
            'outline' => [
                'borderStyle' => Border::BORDER_HAIR,
                'color' => ['rgb' => '000000'],
            ],
            'inside' => [
                'borderStyle' => Border::BORDER_HAIR,
                'color' => ['rgb' => '000000'],
            ],
        ],
    ];

    /**
     * ■罫線：外->微細線／内->SLANTDASHDOT
     *
     * @var \array[][]
     */
    public static $arrStyleHairInsideDot = [
        'borders' => [
            'outline' => [
                'borderStyle' => Border::BORDER_HAIR,
                'color' => ['rgb' => '000000'],
            ],
            'inside' => [
                'borderStyle' => Border::BORDER_SLANTDASHDOT,
                'color' => ['rgb' => '000000'],
            ],
        ],
    ];

    /**
     * [DEBUG]メモリ使用量出力
     *
     * @param string $prefix
     * @return void
     */
    public static function echo_mem(string $prefix = ''): void
    {
        LogHelper::info(__CLASS__, '[' . $prefix . ']',
            (floor(memory_get_usage() / 1024 / 1024) . 'MB' . PHP_EOL));
    }

    /**
     * @param string $path
     * @return void
     */
    public static function makeFolder(string $path): void
    {
        mkdir($path);
    }

    /**
     * [DEBUG]マルチバイトを含む文字埋め
     *
     * @param $input
     * @param $pad_length
     * @param $pad_string
     * @param $pad_style
     * @param $encoding
     * @return string
     */
    public static function mb_str_pad($input, $pad_length, $pad_string = ' ', $pad_style = STR_PAD_RIGHT, $encoding = 'UTF-8'): string
    {
        $mb_pad_length = strlen($input) - mb_strlen($input, $encoding) + $pad_length;

        return str_pad($input, $mb_pad_length, $pad_string, $pad_style);
    }
}
