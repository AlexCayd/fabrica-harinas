DROP DATABASE harinas;
CREATE DATABASE harinas;
USE harinas;
-- Tabla Usuarios
CREATE TABLE Usuarios (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(100) UNIQUE NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    rol ENUM(
        'TI',
        'Gerencia de Control de Calidad',
        'Laboratorio',
        'Gerencia de Aseguramiento de Calidad',
        'Gerente de Planta',
        'Director de Operaciones'
    ) NOT NULL
);
-- Tabla Equipos_Laboratorio
CREATE TABLE Equipos_Laboratorio (
    id_equipo INT PRIMARY KEY AUTO_INCREMENT,
    id_responsable INT NOT NULL,
    clave VARCHAR(50) UNIQUE NOT NULL,
    tipo_equipo ENUM('Alveógrafo', 'Farinógrafo') NOT NULL,
    marca VARCHAR(50),
    modelo VARCHAR(50),
    serie VARCHAR(59) UNIQUE NOT NULL,
    desc_larga VARCHAR(300),
    desc_corta VARCHAR(50) NOT NULL,
    proveedor VARCHAR(50),
    fecha_adquisicion DATE,
    garantia VARCHAR(50) UNIQUE,
    vencimiento_garantia DATE,
    ubicacion VARCHAR(100),
    estado ENUM('Activo', 'Inactivo', 'Baja') NOT NULL DEFAULT 'Activo',
    FOREIGN KEY (id_responsable) REFERENCES Usuarios(id_usuario)
);
-- Tabla Clientes
CREATE TABLE Clientes (
    id_cliente INT PRIMARY KEY AUTO_INCREMENT,
    req_certificado BOOLEAN DEFAULT TRUE,
    nombre VARCHAR(100) NOT NULL,
    rfc VARCHAR(13) UNIQUE NOT NULL,
    nombre_contacto VARCHAR(100),
    puesto_contacto VARCHAR(100),
    correo_contacto VARCHAR(100) UNIQUE NOT NULL,
    telefono_contacto VARCHAR(20) NOT NULL,
    direccion_fiscal TEXT,
    estado ENUM('Activo', 'Inactivo', 'Baja') NOT NULL DEFAULT 'Activo',
    parametros ENUM('Internacionales', 'Personalizados')
);
-- Tabla Direcciones
CREATE TABLE Direcciones (
    id_direccion INT PRIMARY KEY AUTO_INCREMENT,
    id_cliente INT NOT NULL,
    calle VARCHAR(100) NOT NULL,
    num_Exterior VARCHAR(10),
    num_Interior VARCHAR(10),
    colonia VARCHAR(100),
    delegacion_alcaldia VARCHAR(100),
    codigo_postal VARCHAR(10),
    estado VARCHAR(100),
    notas TEXT,
    FOREIGN KEY (id_cliente) REFERENCES Clientes(id_cliente)
);
-- Tabla Parametros
CREATE TABLE Parametros (
    id_parametro INT PRIMARY KEY AUTO_INCREMENT,
    id_equipo INT,
    id_cliente INT,
    nombre_parametro ENUM(
        'Humedad',
        'Cenizas',
        'Gluten_Humedo',
        'Gluten_Seco',
        'Indice_Gluten',
        'Indice_Caida',
        'Alveograma_P',
        'Alveograma_L',
        'Alveograma_PL',
        'Alveograma_W',
        'Alveograma_IE',
        'Almidon_Danado',
        'Farinograma_Absorcion_Agua',
        'Farinograma_Tiempo_Desarrollo',
        'Farinograma_Estabilidad',
        'Farinograma_Grado_Decaimiento'
    ) NOT NULL,
    tipo ENUM('Personalizado', 'Internacional') NOT NULL,
    lim_Superior DECIMAL(10, 2) NOT NULL,
    lim_Inferior DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (id_equipo) REFERENCES Equipos_Laboratorio(id_equipo),
    FOREIGN KEY (id_cliente) REFERENCES Clientes(id_cliente)
);
-- Tabla Inspeccion
CREATE TABLE Inspeccion (
    id_inspeccion INT PRIMARY KEY AUTO_INCREMENT,
    id_cliente INT,
    lote VARCHAR(10),
    secuencia CHAR(3),
    clave VARCHAR(13) NOT NULL,
    fecha_inspeccion TIMESTAMP,
    FOREIGN KEY (id_cliente) REFERENCES Clientes(id_cliente)
);
-- Tabla Resultados_Inspeccion
CREATE TABLE Resultado_Inspeccion (
    id_resultado INT PRIMARY KEY AUTO_INCREMENT,
    id_inspeccion INT NOT NULL,
    nombre_parametro ENUM(
        'Humedad',
        'Cenizas',
        'Gluten_Humedo',
        'Gluten_Seco',
        'Indice_Gluten',
        'Indice_Caida',
        'Alveograma_P',
        'Alveograma_L',
        'Alveograma_PL',
        'Alveograma_W',
        'Alveograma_IE',
        'Almidon_Danado',
        'Farinograma_Absorcion_Agua',
        'Farinograma_Tiempo_Desarrollo',
        'Farinograma_Estabilidad',
        'Farinograma_Grado_Decaimiento'
    ) NOT NULL,
    valor_obtenido DECIMAL(10, 2) NOT NULL,
    aprobado BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_inspeccion) REFERENCES Inspeccion(id_inspeccion)
);
-- Tabla intermedia Equipo_Inspeccion
CREATE TABLE Equipo_Inspeccion (
    id_equipo INT NOT NULL,
    id_inspeccion INT NOT NULL,
    PRIMARY KEY (id_equipo, id_inspeccion),
    FOREIGN KEY (id_equipo) REFERENCES Equipos_Laboratorio(id_equipo),
    FOREIGN KEY (id_inspeccion) REFERENCES Inspeccion(id_inspeccion)
);
-- Tabla Certificados
CREATE TABLE Certificados (
    id_certificado INT PRIMARY KEY AUTO_INCREMENT,
    id_inspeccion INT NOT NULL,
    fecha_emision TIMESTAMP,
    Cantidad_solicitada INT NOT NULL,
    Cantidad_recibida INT NOT NULL,
    fecha_envio DATE,
    fecha_caducidad DATE NOT NULL,
    desviacion DECIMAL(10, 2),
    FOREIGN KEY (id_inspeccion) REFERENCES Inspeccion(id_inspeccion)
);
-- Tabla Hist_Certificados
CREATE TABLE Hist_Certificados (
    id INT PRIMARY KEY AUTO_INCREMENT,
    fecha_subida TIMESTAMP,
    id_certificado INT NOT NULL,
    FOREIGN KEY (id_certificado) REFERENCES Certificados(id_certificado)
);
INSERT INTO Usuarios (nombre, correo, contrasena, rol)
VALUES ('admin', 'admin@correo.com', '1', 'TI');
-- 1. Insertar usuarios para tener responsables de los equipos
INSERT INTO Usuarios (nombre, correo, contrasena, rol)
VALUES (
        'Juan Pérez',
        'jperez@fhelizondo.com',
        '$2y$10$xKO7tB5hl6aXo6ZyAOl56.Tpl1dHas6u5mBkUvu5XyHvSksG3/7AS',
        'Laboratorio'
    ),
    (
        'Ana García',
        'agarcia@fhelizondo.com',
        '$2y$10$9oF1QB1hFuGN7TJ/UEHaQuWW9xA4.fS4gsZZP.mXAeA9Jqkxqx7v2',
        'Gerencia de Control de Calidad'
    );
-- 2. Insertar clientes
INSERT INTO Clientes (
        nombre,
        rfc,
        nombre_contacto,
        puesto_contacto,
        correo_contacto,
        telefono_contacto,
        parametros,
        estado
    )
VALUES (
        'Panadería La Espiga',
        'ESP220504KL2',
        'Roberto Sánchez',
        'Gerente de Compras',
        'rsanchez@laespiga.com',
        '5523456789',
        'Internacionales',
        'Activo'
    ),
    (
        'Industrias Bimbo',
        'BIM020315RT7',
        'Laura Martínez',
        'Director de Calidad',
        'lmartinez@bimbo.com',
        '5587654321',
        'Personalizados',
        'Activo'
    ),
    (
        'Pastelerías Finas',
        'PFI191208PL5',
        'Carlos Rodríguez',
        'Jefe de Producción',
        'crodriguez@pastfinas.com',
        '5545678901',
        'Internacionales',
        'Activo'
    );
-- 3. Insertar equipos de laboratorio
INSERT INTO Equipos_Laboratorio (
        id_responsable,
        clave,
        tipo_equipo,
        marca,
        modelo,
        serie,
        desc_larga,
        desc_corta,
        proveedor,
        fecha_adquisicion,
        garantia,
        vencimiento_garantia,
        ubicacion,
        estado
    )
VALUES (
        1,
        'ALV001',
        'Alveógrafo',
        'Chopin',
        'NG',
        'CH2023001',
        'Alveógrafo de Chopin modelo NG para análisis de calidad de gluten',
        'Alveógrafo Chopin NG',
        'Chopin Instruments',
        '2023-01-15',
        'G-ALV001-2023',
        '2026-01-15',
        'Laboratorio Central',
        'Activo'
    ),
    (
        2,
        'ALV002',
        'Alveógrafo',
        'Brabender',
        'AB200',
        'BR2022045',
        'Alveógrafo Brabender modelo AB200 para análisis de harinas',
        'Alveógrafo Brabender',
        'Brabender GmbH',
        '2022-08-10',
        'G-ALV002-2022',
        '2025-08-10',
        'Laboratorio de Control de Calidad',
        'Activo'
    ),
    (
        1,
        'FAR001',
        'Farinógrafo',
        'Brabender',
        'F-300',
        'BR2023102',
        'Farinógrafo Brabender modelo F-300 para evaluación de absorción de agua',
        'Farinógrafo Brabender F-300',
        'Brabender GmbH',
        '2023-03-20',
        'G-FAR001-2023',
        '2026-03-20',
        'Laboratorio Central',
        'Activo'
    );
-- 4. Insertar parámetros para los equipos (internacionales)
-- Parámetros para Alveógrafo 1
INSERT INTO Parametros (
        id_equipo,
        nombre_parametro,
        tipo,
        lim_Superior,
        lim_Inferior
    )
VALUES (1, 'Alveograma_P', 'Internacional', 90.00, 60.00),
    (
        1,
        'Alveograma_L',
        'Internacional',
        110.00,
        70.00
    ),
    (
        1,
        'Alveograma_W',
        'Internacional',
        300.00,
        230.00
    ),
    (1, 'Alveograma_PL', 'Internacional', 1.20, 0.60),
    (
        1,
        'Alveograma_IE',
        'Internacional',
        65.00,
        45.00
    );
-- Parámetros para Alveógrafo 2
INSERT INTO Parametros (
        id_equipo,
        nombre_parametro,
        tipo,
        lim_Superior,
        lim_Inferior
    )
VALUES (2, 'Alveograma_P', 'Internacional', 95.00, 65.00),
    (
        2,
        'Alveograma_L',
        'Internacional',
        115.00,
        75.00
    ),
    (
        2,
        'Alveograma_W',
        'Internacional',
        320.00,
        240.00
    ),
    (2, 'Alveograma_PL', 'Internacional', 1.30, 0.65),
    (
        2,
        'Alveograma_IE',
        'Internacional',
        70.00,
        50.00
    );
-- Parámetros para Farinógrafo
INSERT INTO Parametros (
        id_equipo,
        nombre_parametro,
        tipo,
        lim_Superior,
        lim_Inferior
    )
VALUES (
        3,
        'Farinograma_Absorcion_Agua',
        'Internacional',
        65.00,
        55.00
    ),
    (
        3,
        'Farinograma_Tiempo_Desarrollo',
        'Internacional',
        8.00,
        4.00
    ),
    (
        3,
        'Farinograma_Estabilidad',
        'Internacional',
        15.00,
        7.00
    ),
    (
        3,
        'Farinograma_Grado_Decaimiento',
        'Internacional',
        70.00,
        40.00
    );
-- Parámetros personalizados para cliente Bimbo (más estrictos)
INSERT INTO Parametros (
        id_equipo,
        id_cliente,
        nombre_parametro,
        tipo,
        lim_Superior,
        lim_Inferior
    )
VALUES (
        1,
        2,
        'Alveograma_P',
        'Personalizado',
        85.00,
        65.00
    ),
    (
        1,
        2,
        'Alveograma_L',
        'Personalizado',
        105.00,
        80.00
    ),
    (
        1,
        2,
        'Alveograma_W',
        'Personalizado',
        290.00,
        250.00
    ),
    (
        1,
        2,
        'Alveograma_PL',
        'Personalizado',
        1.10,
        0.70
    ),
    (
        1,
        2,
        'Alveograma_IE',
        'Personalizado',
        60.00,
        50.00
    ),
    (
        3,
        2,
        'Farinograma_Absorcion_Agua',
        'Personalizado',
        62.00,
        58.00
    ),
    (
        3,
        2,
        'Farinograma_Tiempo_Desarrollo',
        'Personalizado',
        7.50,
        5.00
    ),
    (
        3,
        2,
        'Farinograma_Estabilidad',
        'Personalizado',
        14.00,
        9.00
    ),
    (
        3,
        2,
        'Farinograma_Grado_Decaimiento',
        'Personalizado',
        65.00,
        45.00
    );
-- 5. Insertar inspecciones
-- Inspección 1: Lote BAT001A para cliente La Espiga (todos los parámetros dentro de rango)
INSERT INTO Inspeccion (
        id_cliente,
        lote,
        secuencia,
        clave,
        fecha_inspeccion
    )
VALUES (
        1,
        'BAT001',
        'A',
        'BAT001A',
        '2023-11-20 09:30:00'
    );
-- Inspección 2: Lote BAT002A para cliente Bimbo (algunos parámetros fuera de rango)
INSERT INTO Inspeccion (
        id_cliente,
        lote,
        secuencia,
        clave,
        fecha_inspeccion
    )
VALUES (
        2,
        'BAT002',
        'A',
        'BAT002A',
        '2023-11-21 10:15:00'
    );
-- Inspección 3: Lote BAT002B para cliente Bimbo (nuevo análisis del mismo lote)
INSERT INTO Inspeccion (
        id_cliente,
        lote,
        secuencia,
        clave,
        fecha_inspeccion
    )
VALUES (
        2,
        'BAT002',
        'B',
        'BAT002B',
        '2023-11-21 14:30:00'
    );
-- Inspección 4: Lote BAT003A para cliente Pastelerías Finas
INSERT INTO Inspeccion (
        id_cliente,
        lote,
        secuencia,
        clave,
        fecha_inspeccion
    )
VALUES (
        3,
        'BAT003',
        'A',
        'BAT003A',
        '2023-11-22 08:45:00'
    );
-- Inspección 5: Lote BAT004A sin cliente (inspección interna)
INSERT INTO Inspeccion (
        id_cliente,
        lote,
        secuencia,
        clave,
        fecha_inspeccion
    )
VALUES (
        NULL,
        'BAT004',
        'A',
        'BAT004A',
        '2023-11-23 11:20:00'
    );
-- Insertar relaciones Equipo-Inspección
INSERT INTO Equipo_Inspeccion (id_equipo, id_inspeccion)
VALUES (1, 1),
    (1, 2),
    (3, 2),
    (2, 3),
    (1, 4),
    (3, 4),
    (3, 5);
-- Resultados para Inspección 1
INSERT INTO Resultado_Inspeccion (
        id_inspeccion,
        nombre_parametro,
        valor_obtenido,
        aprobado
    )
VALUES (1, 'Alveograma_P', 75.50, TRUE),
    (1, 'Alveograma_L', 90.20, TRUE),
    (1, 'Alveograma_W', 265.80, TRUE),
    (1, 'Alveograma_PL', 0.84, TRUE),
    (1, 'Alveograma_IE', 55.30, TRUE);
-- Resultados para Inspección 2 - Alveógrafo
INSERT INTO Resultado_Inspeccion (
        id_inspeccion,
        nombre_parametro,
        valor_obtenido,
        aprobado
    )
VALUES (2, 'Alveograma_P', 62.40, FALSE),
    (2, 'Alveograma_L', 92.70, TRUE),
    (2, 'Alveograma_W', 245.30, FALSE),
    (2, 'Alveograma_PL', 0.67, FALSE),
    (2, 'Alveograma_IE', 52.80, TRUE);
-- Inspección 2 - Farinógrafo
INSERT INTO Resultado_Inspeccion (
        id_inspeccion,
        nombre_parametro,
        valor_obtenido,
        aprobado
    )
VALUES (2, 'Farinograma_Absorcion_Agua', 60.20, TRUE),
    (2, 'Farinograma_Tiempo_Desarrollo', 5.80, TRUE),
    (2, 'Farinograma_Estabilidad', 10.40, TRUE),
    (2, 'Farinograma_Grado_Decaimiento', 55.60, TRUE);
-- Inspección 3 - Alveógrafo
INSERT INTO Resultado_Inspeccion (
        id_inspeccion,
        nombre_parametro,
        valor_obtenido,
        aprobado
    )
VALUES (3, 'Alveograma_P', 75.30, TRUE),
    (3, 'Alveograma_L', 95.80, TRUE),
    (3, 'Alveograma_W', 270.40, TRUE),
    (3, 'Alveograma_PL', 0.79, TRUE),
    (3, 'Alveograma_IE', 58.20, TRUE);
-- Inspección 4 - Alveógrafo
INSERT INTO Resultado_Inspeccion (
        id_inspeccion,
        nombre_parametro,
        valor_obtenido,
        aprobado
    )
VALUES (4, 'Alveograma_P', 72.60, TRUE),
    (4, 'Alveograma_L', 85.30, TRUE),
    (4, 'Alveograma_W', 252.40, TRUE),
    (4, 'Alveograma_PL', 0.85, TRUE),
    (4, 'Alveograma_IE', 50.70, TRUE);
-- Inspección 4 - Farinógrafo
INSERT INTO Resultado_Inspeccion (
        id_inspeccion,
        nombre_parametro,
        valor_obtenido,
        aprobado
    )
VALUES (4, 'Farinograma_Absorcion_Agua', 58.90, TRUE),
    (4, 'Farinograma_Tiempo_Desarrollo', 3.70, FALSE),
    (4, 'Farinograma_Estabilidad', 8.20, TRUE),
    (4, 'Farinograma_Grado_Decaimiento', 52.30, TRUE);
-- Inspección 5 - Farinógrafo
INSERT INTO Resultado_Inspeccion (
        id_inspeccion,
        nombre_parametro,
        valor_obtenido,
        aprobado
    )
VALUES (5, 'Farinograma_Absorcion_Agua', 61.40, TRUE),
    (5, 'Farinograma_Tiempo_Desarrollo', 6.50, TRUE),
    (5, 'Farinograma_Estabilidad', 11.80, TRUE),
    (5, 'Farinograma_Grado_Decaimiento', 48.90, TRUE);