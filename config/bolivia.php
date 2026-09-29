<?php

/**
 * HU-59: catálogos de Bolivia para el flujo de inscripción.
 * - complementos: códigos de complemento del CI (departamentales SEGIP + alfanuméricos).
 * - departamentos: provincias y ciudades/municipios principales por departamento.
 */
return [

    'complementos' => [
        'LP', 'CB', 'SC', 'TJ', 'OR', 'PT', 'CH', 'BN', 'PA',
        '1A', '1B', '1C', '1D', '1E', '1F',
        '2A', '2B', '2C', '2D', '2E', '2F',
        '3A', '3B', '3C', '3D', '3E', '3F',
        '4A', '4B', '4C', '5A', '5B',
    ],

    'departamentos' => [
        'Tarija' => [
            'provincias' => ['Aniceto Arce', 'Avilés', 'Cercado', 'Eustaquio Méndez', 'Gran Chaco', "Burnet O'Connor"],
            'ciudades' => ['Tarija', 'Bermejo', 'Caraparí', 'El Puente', 'Entre Ríos', 'Padcaya', 'San Lorenzo', 'Uriondo', 'Villamontes', 'Yacuiba', 'Yunchará'],
        ],
        'Chuquisaca' => [
            'provincias' => ['Azurduy', 'Belisario Boeto', 'Hernando Siles', 'Luis Calvo', 'Nor Cinti', 'Oropeza', 'Sud Cinti', 'Tomina', 'Yamparáez', 'Zudáñez'],
            'ciudades' => ['Sucre', 'Camargo', 'Monteagudo', 'Tarabuco', 'Villa Serrano'],
        ],
        'Cochabamba' => [
            'provincias' => ['Arani', 'Arque', 'Ayopaya', 'Bolívar', 'Capinota', 'Carrasco', 'Cercado', 'Chapare', 'Esteban Arce', 'Germán Jordán', 'Mizque', 'Campero', 'Punata', 'Quillacollo', 'Tapacarí', 'Tiraque'],
            'ciudades' => ['Cochabamba', 'Cliza', 'Colcapirhua', 'Mizque', 'Punata', 'Quillacollo', 'Sacaba', 'Tiquipaya'],
        ],
        'La Paz' => [
            'provincias' => ['Aroma', 'Bautista Saavedra', 'Camacho', 'Caranavi', 'Franz Tamayo', 'Gualberto Villarroel', 'Ingavi', 'Inquisivi', 'Abel Iturralde', 'José Manuel Pando', 'Larecaja', 'Loayza', 'Los Andes', 'Manco Kapac', 'Muñecas', 'Murillo', 'Nor Yungas', 'Omasuyos', 'Pacajes', 'Sud Yungas'],
            'ciudades' => ['La Paz', 'Achocalla', 'Caranavi', 'Copacabana', 'Coroico', 'El Alto', 'Guanay', 'Viacha'],
        ],
        'Oruro' => [
            'provincias' => ['Atahuallpa', 'Carangas', 'Cercado', 'Eduardo Avaroa', 'Ladislao Cabrera', 'Litoral', 'Mejillones', 'Nor Carangas', 'Pantaleón Dalence', 'Poopó', 'Sajama', 'San Pedro de Totora', 'Saucarí', 'Sebastián Pagador', 'Sur Carangas', 'Tomás Barrón'],
            'ciudades' => ['Oruro', 'Caracollo', 'Challapata', 'Huanuni', 'Machacamarca'],
        ],
        'Potosí' => [
            'provincias' => ['Alonso de Ibáñez', 'Antonio Quijarro', 'Bernardino Bilbao', 'Charcas', 'Chayanta', 'Cornelio Saavedra', 'Daniel Campos', 'Enrique Baldivieso', 'José María Linares', 'Modesto Omiste', 'Nor Chichas', 'Nor Lípez', 'Rafael Bustillo', 'Sur Chichas', 'Sur Lípez', 'Tomás Frías'],
            'ciudades' => ['Potosí', 'Betanzos', 'Llallagua', 'Tupiza', 'Uyuni', 'Villazón'],
        ],
        'Santa Cruz' => [
            'provincias' => ['Andrés Ibáñez', 'Ángel Sandoval', 'Chiquitos', 'Cordillera', 'Florida', 'Germán Busch', 'Guarayos', 'Ichilo', 'José Miguel de Velasco', 'Manuel María Caballero', 'Ñuflo de Chávez', 'Obispo Santistevan', 'Sara', 'Vallegrande', 'Warnes'],
            'ciudades' => ['Santa Cruz de la Sierra', 'Cotoca', 'El Torno', 'La Guardia', 'Montero', 'Portachuelo', 'San José de Chiquitos', 'Warnes', 'Yapacaní'],
        ],
        'Beni' => [
            'provincias' => ['Cercado', 'Iténez', 'José Ballivián', 'Mamoré', 'Marbán', 'Moxos', 'Vaca Díez', 'Yacuma'],
            'ciudades' => ['Trinidad', 'Guayaramerín', 'Riberalta', 'Rurrenabaque', 'San Borja', 'Santa Ana del Yacuma'],
        ],
        'Pando' => [
            'provincias' => ['Abuná', 'Federico Román', 'Madre de Dios', 'Manuripi', 'Nicolás Suárez'],
            'ciudades' => ['Cobija', 'Porvenir', 'Puerto Rico', 'Sena'],
        ],
    ],

];
