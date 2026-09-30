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
            'municipios' => ['Tarija', 'Padcaya', 'Bermejo', 'Uriondo', 'Yunchará', 'San Lorenzo', 'El Puente', 'Yacuiba', 'Villamontes', 'Caraparí', 'Entre Ríos'],
        ],
        'Chuquisaca' => [
            'provincias' => ['Azurduy', 'Belisario Boeto', 'Hernando Siles', 'Luis Calvo', 'Nor Cinti', 'Oropeza', 'Sud Cinti', 'Tomina', 'Yamparáez', 'Zudáñez'],
            'ciudades' => ['Sucre', 'Camargo', 'Monteagudo', 'Tarabuco', 'Villa Serrano'],
            'municipios' => ['Sucre', 'Yotala', 'Poroma', 'Azurduy', 'Tarvita', 'Villa Abecia', 'Tarabuco', 'Yamparáez', 'Presto', 'Mojocoya', 'Icla', 'Sopachuy', 'Villa Serrano', 'Villa Charcas', 'Culpina', 'Incahuasi', 'Camargo', 'San Lucas', 'Las Carreras', 'Huacaya', 'Macharetí', 'Huacareta', 'Monteagudo', 'Villa Vaca Guzmán', 'Muyupampa', 'Aiquile', 'Pasorapa', 'Omereque', 'Zudáñez'],
        ],
        'Cochabamba' => [
            'provincias' => ['Arani', 'Arque', 'Ayopaya', 'Bolívar', 'Capinota', 'Carrasco', 'Cercado', 'Chapare', 'Esteban Arce', 'Germán Jordán', 'Mizque', 'Campero', 'Punata', 'Quillacollo', 'Tapacarí', 'Tiraque'],
            'ciudades' => ['Cochabamba', 'Cliza', 'Colcapirhua', 'Mizque', 'Punata', 'Quillacollo', 'Sacaba', 'Tiquipaya'],
            'municipios' => ['Cochabamba', 'Quillacollo', 'Sacaba', 'Colcapirhua', 'Tiquipaya', 'Vinto', 'Sipe Sipe', 'Colomi', 'Villa Tunari', 'Shinahota', 'Chimoré', 'Puerto Villarroel', 'Entre Ríos', 'Totora', 'Pojo', 'Pocona', 'Mizque', 'Vila Vila', 'Alalay', 'Aiquile', 'Pasorapa', 'Omereque', 'Arani', 'Punata', 'Cliza', 'Tarata', 'Anzaldo', 'Arbieto', 'Capinota', 'Santiváñez', 'Sicaya', 'Tacachi', 'Cuchumuela', 'San Benito', 'Villa Rivero', 'Tacopaya', 'Arque', 'Bolívar', 'Tapacarí', 'Tiraque', 'Tolata', 'Morochata', 'Ayopaya', 'Independencia', 'Sacabamba'],
        ],
        'La Paz' => [
            'provincias' => ['Aroma', 'Bautista Saavedra', 'Camacho', 'Caranavi', 'Franz Tamayo', 'Gualberto Villarroel', 'Ingavi', 'Inquisivi', 'Abel Iturralde', 'José Manuel Pando', 'Larecaja', 'Loayza', 'Los Andes', 'Manco Kapac', 'Muñecas', 'Murillo', 'Nor Yungas', 'Omasuyos', 'Pacajes', 'Sud Yungas'],
            'ciudades' => ['La Paz', 'Achocalla', 'Caranavi', 'Copacabana', 'Coroico', 'El Alto', 'Guanay', 'Viacha'],
            'municipios' => ['La Paz', 'El Alto', 'Viacha', 'Achocalla', 'Mecapaca', 'Palca', 'Pucarani', 'Laja', 'Tiahuanaco', 'Guaqui', 'Desaguadero', 'Copacabana', 'Achacachi', 'Sorata', 'Mapiri', 'Guanay', 'Tipuani', 'Caranavi', 'Coroico', 'Coripata', 'Chulumani', 'Irupana', 'Yanacachi', 'Cairoma', 'Quime', 'Inquisivi', 'Colquiri', 'Ayo Ayo', 'Calamarca', 'Sica Sica', 'Patacamaya', 'Sapahaqui', 'Luribay', 'Malla', 'Yaco', 'Tacacoma', 'Chuma', 'Ayata', 'Charazani', 'Curva', 'Pelechuco', 'Apolo', 'Ixiamas', 'Rurrenabaque', 'Reyes', 'San Buenaventura'],
        ],
        'Oruro' => [
            'provincias' => ['Atahuallpa', 'Carangas', 'Cercado', 'Eduardo Avaroa', 'Ladislao Cabrera', 'Litoral', 'Mejillones', 'Nor Carangas', 'Pantaleón Dalence', 'Poopó', 'Sajama', 'San Pedro de Totora', 'Saucarí', 'Sebastián Pagador', 'Sur Carangas', 'Tomás Barrón'],
            'ciudades' => ['Oruro', 'Caracollo', 'Challapata', 'Huanuni', 'Machacamarca'],
            'municipios' => ['Oruro', 'Caracollo', 'Challapata', 'Huanuni', 'Machacamarca', 'Poopó', 'Pazña', 'Antequera', 'Eucaliptus', 'Huari', 'Toledo', 'Corque', 'Turco', 'Huachacalla', 'Escara', 'Esmeralda', 'Huayllamarca', 'Totora', 'Curahuara de Carangas', 'Choquecota', 'Santiago de Huari', 'Salinas de Garci Mendoza', 'Pampa Aullagas', 'Santuario de Quillacas', 'Belén de Andamarca'],
        ],
        'Potosí' => [
            'provincias' => ['Alonso de Ibáñez', 'Antonio Quijarro', 'Bernardino Bilbao', 'Charcas', 'Chayanta', 'Cornelio Saavedra', 'Daniel Campos', 'Enrique Baldivieso', 'José María Linares', 'Modesto Omiste', 'Nor Chichas', 'Nor Lípez', 'Rafael Bustillo', 'Sur Chichas', 'Sur Lípez', 'Tomás Frías'],
            'ciudades' => ['Potosí', 'Betanzos', 'Llallagua', 'Tupiza', 'Uyuni', 'Villazón'],
            'municipios' => ['Potosí', 'Betanzos', 'Chaquí', 'Tacobamba', 'Yocalla', 'Urmiri', 'Porco', 'Uyuni', 'Colcha K', 'San Pedro de Quemes', 'Llica', 'Tahua', 'Tupiza', 'Atocha', 'Cotagaita', 'Vitichi', 'Villazón', 'Caiza D', 'Llallagua', 'Uncía', 'Chayanta', 'Pocoata', 'Ocurí', 'Ravelo', 'Tinguipaya', 'Arampampa', 'Acasio', 'Toro Toro', 'San Pedro de Buena Vista', 'Chuquihuta', 'Tacobamba', 'Mojinete', 'San Antonio de Esmoruco'],
        ],
        'Santa Cruz' => [
            'provincias' => ['Andrés Ibáñez', 'Ángel Sandoval', 'Chiquitos', 'Cordillera', 'Florida', 'Germán Busch', 'Guarayos', 'Ichilo', 'José Miguel de Velasco', 'Manuel María Caballero', 'Ñuflo de Chávez', 'Obispo Santistevan', 'Sara', 'Vallegrande', 'Warnes'],
            'ciudades' => ['Santa Cruz de la Sierra', 'Cotoca', 'El Torno', 'La Guardia', 'Montero', 'Portachuelo', 'San José de Chiquitos', 'Warnes', 'Yapacaní'],
            'municipios' => ['Santa Cruz de la Sierra', 'Cotoca', 'Porongo', 'La Guardia', 'El Torno', 'Warnes', 'Montero', 'Mineros', 'Yapacaní', 'San Pedro', 'San Carlos', 'Buena Vista', 'Portachuelo', 'Colpa Bélgica', 'Santa Rosa del Sara', 'San Juan', 'Fernández Alonso', 'Okinawa Uno', 'Cuatro Cañadas', 'San Julián', 'El Puente', 'Ascensión de Guarayos', 'Urubichá', 'San Javier', 'Concepción', 'San Ignacio de Velasco', 'San Miguel', 'San Rafael', 'San José de Chiquitos', 'Roboré', 'Puerto Suárez', 'Puerto Quijarro', 'Vallegrande', 'Trigal', 'Moro Moro', 'Postrervalle', 'Pucará', 'Samaipata', 'Pampa Grande', 'Mairana', 'Quirusillas', 'Cabezas', 'Charagua', 'Gutiérrez', 'Lagunillas', 'Camiri', 'Boyuibe', 'Cuevo', 'El Carmen Rivero Tórrez', 'San Matías', 'San Ramón', 'Comarapa', 'Saipina'],
        ],
        'Beni' => [
            'provincias' => ['Cercado', 'Iténez', 'José Ballivián', 'Mamoré', 'Marbán', 'Moxos', 'Vaca Díez', 'Yacuma'],
            'ciudades' => ['Trinidad', 'Guayaramerín', 'Riberalta', 'Rurrenabaque', 'San Borja', 'Santa Ana del Yacuma'],
            'municipios' => ['Trinidad', 'San Javier', 'San Pedro', 'Santa Ana', 'Exaltación', 'San Ignacio', 'San Andrés', 'Loreto', 'San Joaquín', 'San Ramón', 'Puerto Siles', 'Magdalena', 'Baures', 'Huacaraje', 'Riberalta', 'Guayaramerín', 'Reyes', 'Rurrenabaque', 'Santa Rosa'],
        ],
        'Pando' => [
            'provincias' => ['Abuná', 'Federico Román', 'Madre de Dios', 'Manuripi', 'Nicolás Suárez'],
            'ciudades' => ['Cobija', 'Porvenir', 'Puerto Rico', 'Sena'],
            'municipios' => ['Cobija', 'Porvenir', 'Bella Flor', 'Bolpebra', 'Filadelfia', 'Puerto Rico', 'Sena', 'Santa Rosa del Abuná', 'Ingavi', 'Nueva Esperanza', 'Villa Nueva', 'Santos Mercado', 'San Lorenzo', 'Madre de Dios', 'El Sena'],
        ],
    ],

];
