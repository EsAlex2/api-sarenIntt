<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserSarenSeeder extends Seeder
{
    /**
     * Correr los seeds de la base de datos.
     */
    public function run(): void
    {
        $rawData = "6436669	JOSE RAFAEL	ROMERO CELIS
6447169	RAUL GUAICAIPURO	DIAZ SOSA
6908039	ELISEO MARTIN	ZAPATA MONTILLA
10923406	HECTOR MANUEL	CIPRIANI CADENA
11406688	ROMER NOEL	BARRETO RODRIGUEZ
11916664	YUMAR MARLET DEL CARMEN	FUENTES DIAZ
13086994	JESSGDY HORTENCIA	MONSERRATE RODRIGUEZ
13582775	YURIMAR YANETH	ZAMBRANO SIERRA
13620851	JUAN CARLOS	SOTILLO RONDON
13871461	LEONARDO ADELIS	PERNALETE HERNANDEZ
13910530	JOSE FELIX	BORGES DUQUE
14049146	JOHANNA JOSEFINA	LOPEZ TOUSSAINT
14384624	CESAR AUGUSTO	DOMINGUEZ GOMEZ
15098551	JULIO CESAR	YUNIS DE BEAUMONT
15148146	IVIS JAXIBETH	ESCALONA ORIHUEN
15420478	MANUEL ALEJANDRO	LEIVA MEDIOMUNDO
15888275	LORENA JOSEFINA	ACOSTA
15932479	JOSE GREGORIO	PEREZ PATIÑO
16495636	YONATHAN JOSE	NIEVES
17075984	DERYK ANIBAL	DIAZ GRATEROL
17124416	ALEXIS EDUARDO	SANCHEZ MAYORCA
18118118	BARBARA JOSEFINA	GONZALEZ JARAMILLO
19266813	MONICA MARIVIT DE LA CARIDAD	RUIZ NAVAS
19933970	ALI RAMON	MARQUEZ
20093370	LUIS EDUARDO	CANELON MARQUEZ
20123029	KEYMI JOSE	MEJIAS HERNANDEZ
22752853	JOHANNA ESTHER	SEITIFFE RIVAS
24906477	RAFAEL ANTONIO	BRITO OZUNA
25367467	DAIRY WILMARY	LOZADA CASTRO
26331186	EDGAR YOEL	PEREIRA RUIZ
27272540	ROCMARY DANYELA	ROCCO MUÑOZ
27796915	JOSE RAFAEL	ROMERO HERNANDEZ
28143033	ROXLENE FRANSHESCA	VERA HERNANDEZ
28469269	ORLANDO JOSE	MEDINA PULIDO
30330385	ABEL DAVID	VALERA SALAZAR
30619459	ANGEL DAVID	MUJICA PEREZ
31803702	GERMANY ANDREA	GONZALEZ VILLAROEL
33260262	YERMAIN DAVID	CAMACHO CAMACHO
6010783	GUIDO ANTONIO	SOTO ZAMBRANO
10524468	JANETH ZORAIDA	ROJAS APONTE
11005609	MAILINI JOSEFINA	MAICAN
12747738	JASMIN COROMOTO	SALCEDO VERDU
14018959	ANTOBEL ADOLFO	CHACON CAMPOS
15377567	ROSA BRILLIDT	RAMOS
15470736	DAPHNY  LARIZA	GUTIERREZ NOGUERA
17199780	LUIS EDUARDO	TRUJILLO GRANADILLO
17498152	KATY CARINA	ALVARADO DIAZ
21191285	MARIANGELA DANIELA	SUAREZ MARIN
22789070	MEIDIMAR DEL VALLE	NIEVES PONCE
23686674	GABRIELLA DESIREE	GARCIA PAEZ
26735217	FRANCISCO JAVIER	CRUZ NOGUERA";

        $lines = explode("\n", $rawData);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            // Dividir por tabulador o por dos o más espacios consecutivos
            $parts = preg_split('/\t|\s{2,}/', $line);
            $parts = array_map('trim', $parts);

            if (count($parts) >= 3) {
                $cedula = $parts[0];
                $fullName = $parts[1];
                $lastName = $parts[2];

                // Procesar nombres
                $namesArr = explode(' ', $fullName);
                $primer_nombre = $namesArr[0];
                $segundo_nombre = count($namesArr) > 1 ? implode(' ', array_slice($namesArr, 1)) : null;

                // Procesar apellidos
                $lastNameArr = explode(' ', $lastName);
                $primer_apellido = $lastNameArr[0];
                $segundo_apellido = count($lastNameArr) > 1 ? implode(' ', array_slice($lastNameArr, 1)) : null;

                // Generar datos aleatorios y lógicos
                $emailUsername = strtolower(Str::ascii($primer_nombre . '.' . $primer_apellido));
                // Asegurar unicidad usando los últimos 3 dígitos de la cédula
                $lastThreeDigits = substr($cedula, -3);
                $correo_saren = $emailUsername . $lastThreeDigits . '@saren.gob.ve';

                // Fecha de nacimiento aleatoria (entre 1965 y 2005)
                $fecha_nacimiento = date('Y-m-d', rand(strtotime('1965-01-01'), strtotime('2005-12-31')));

                // RIF (V + Cédula + Dígito verificador aleatorio)
                $digito_rif = $cedula . rand(0, 9);

                // Teléfono celular aleatorio en formato venezolano
                $codigosCelular = ['0412', '0414', '0424', '0416', '0426'];
                $telef_celular = $codigosCelular[array_rand($codigosCelular)] . rand(1000000, 9999999);

                DB::table('user_sarens')->updateOrInsert(
                    ['numero_documento' => $cedula],
                    [
                        'primer_nombre' => $primer_nombre,
                        'segundo_nombre' => $segundo_nombre,
                        'primer_apellido' => $primer_apellido,
                        'segundo_apellido' => $segundo_apellido,
                        'tipo_documento' => 'V', // Persona Natural
                        'correo_saren' => $correo_saren,
                        'fecha_nacimiento' => $fecha_nacimiento,
                        'nombre_empresa' => 'No Aplica',
                        'digito_rif' => $digito_rif,
                        'telef_celular' => $telef_celular,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );
            }
        }
    }
}
