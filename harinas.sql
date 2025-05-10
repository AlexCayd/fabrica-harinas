DROP DATABASE IF EXISTS harinas;
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
    tipo_equipo ENUM('Alveógrafo', 'Farinógrafo') NOT NULL,
    parametros ENUM('Internacionales', 'Personalizados'),
    causa_baja VARCHAR(255)
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
        'Almidon_Danado',
        'Alveograma_P',
        'Alveograma_L',
        'Alveograma_PL',
        'Alveograma_W',
        'Alveograma_IE',
        'Farinograma_Absorcion_Agua',
        'Farinograma_Tiempo_Desarrollo',
        'Farinograma_Estabilidad',
        'Farinograma_Grado_Decaimiento'
    ) NOT NULL,
    lim_Superior DECIMAL(10, 2) NOT NULL,
    lim_Inferior DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (id_equipo) REFERENCES Equipos_Laboratorio(id_equipo),
    FOREIGN KEY (id_cliente) REFERENCES Clientes(id_cliente),
    CHECK (
        (
            id_cliente IS NOT NULL
            AND id_equipo IS NULL
        )
        OR (
            id_cliente IS NULL
            AND id_equipo IS NOT NULL
        )
    )
);
-- Tabla Inspeccion
CREATE TABLE Inspeccion (
    id_inspeccion INT PRIMARY KEY AUTO_INCREMENT,
    id_cliente INT,
    id_equipo INT,
    lote VARCHAR(10),
    secuencia CHAR(3),
    clave VARCHAR(13) NOT NULL,
    fecha_inspeccion TIMESTAMP,
    FOREIGN KEY (id_cliente) REFERENCES Clientes(id_cliente),
    FOREIGN KEY (id_equipo) REFERENCES Equipos_Laboratorio(id_equipo),
    CHECK (
        (
            id_cliente IS NOT NULL
            AND id_equipo IS NULL
        )
        OR (
            id_cliente IS NULL
            AND id_equipo IS NOT NULL
        )
    )
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
        'Almidon_Danado',
        'Alveograma_P',
        'Alveograma_L',
        'Alveograma_PL',
        'Alveograma_W',
        'Alveograma_IE',
        'Farinograma_Absorcion_Agua',
        'Farinograma_Tiempo_Desarrollo',
        'Farinograma_Estabilidad',
        'Farinograma_Grado_Decaimiento'
    ) NOT NULL,
    valor_obtenido DECIMAL(10, 2) NOT NULL,
    aprobado BOOLEAN DEFAULT TRUE,
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
    numero_factura VARCHAR(100),
    numero_orden_compra VARCHAR(100),
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
VALUES ('admin', 'admin@correo.com', '$2y$10$Rwwpj5/OC3MFaG0K1XkyW.7ja0W.0JlyIaejZ7C4Mx7W9rridtn5q', 'TI');
-- INSERTS --
-- USUARIOS (Uno por cada rol)
INSERT INTO Usuarios (nombre, correo, contrasena, rol)
VALUES (
        'Juan Pérez',
        'juan.perez@empresa.com',
        'contrasena1',
        'TI'
    ),
    (
        'Laura Gómez',
        'laura.gomez@empresa.com',
        'contrasena2',
        'Gerencia de Control de Calidad'
    ),
    (
        'Carlos Méndez',
        'carlos.mendez@empresa.com',
        'contrasena3',
        'Laboratorio'
    ),
    (
        'Ana Ruiz',
        'ana.ruiz@empresa.com',
        'contrasena4',
        'Gerencia de Aseguramiento de Calidad'
    ),
    (
        'Luis Torres',
        'luis.torres@empresa.com',
        'contrasena5',
        'Gerente de Planta'
    ),
    (
        'Mónica Salinas',
        'monica.salinas@empresa.com',
        'contrasena6',
        'Director de Operaciones'
    );
-- EQUIPOS DE LABORATORIO PARAMETROS INTERNACIONALES DE REFERENCIA (uno de cada tipo)
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
        'ALV-INT',
        'Alveógrafo',
        'Chopin',
        'AlveoLab',
        'ALV12345',
        'Alveógrafo para Parámetros Internacionales',
        'Alveógrafo Internacional',
        'Proveedor A',
        '2000-01-01',
        'GAR-ALV-001',
        '2099-12-31',
        'Laboratorio Principal',
        'Activo'
    ),
    (
        1,
        'FAR-INT',
        'Farinógrafo',
        'Brabender',
        'FarinoLab',
        'FAR54321',
        'Farinógrafo para Parámetros Internacionales',
        'Farinógrafo Internacional',
        'Proveedor B',
        '2000-01-01',
        'GAR-FAR-001',
        '2099-12-31',
        'Laboratorio Principal',
        'Activo'
    );
-- EQUIPOS ADICIONALES: 2 Alveógrafos y 2 Farinógrafos
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
VALUES -- Alveógrafos adicionales
    (
        3,
        'ALV-001',
        'Alveógrafo',
        'Chopin',
        'NG LabNet',
        'CH22579B',
        'Alveógrafo NG LabNet con sistema de control digital para análisis precisos de masa. Incluye módulo de hidratación adaptativa.',
        'Alveógrafo Digital',
        'Chopin Technologies',
        '2024-01-10',
        'GAR-ALV-002',
        '2029-01-10',
        'Laboratorio de Investigación',
        'Activo'
    ),
    (
        2,
        'ALV-002',
        'Alveógrafo',
        'Chopin',
        'AlveoPC',
        'APC78934X',
        'Alveógrafo con interfaz PC integrada y sistema de control automatizado. Capacidad para muestras múltiples simultáneas.',
        'Alveógrafo Automático',
        'Foss Iberia',
        '2023-03-15',
        'GAR-ALV-003',
        '2028-03-15',
        'Área de Control de Calidad',
        'Activo'
    ),
    -- Farinógrafos adicionales
    (
        3,
        'FAR-001',
        'Farinógrafo',
        'Brabender',
        'Farinograph-AT',
        'FA67821R',
        'Farinógrafo automático con sistema de termostatización integrado y amasadora de acero inoxidable de 300g.',
        'Farinógrafo Automático',
        'Brabender GmbH',
        '2025-04-02',
        'GAR-FAR-002',
        '2034-04-02',
        'Laboratorio Principal',
        'Activo'
    ),
    (
        2,
        'FAR-002',
        'Farinógrafo',
        'Brabender',
        'Farinograph-E',
        'FE45698T',
        'Farinógrafo electrónico con interfaz digital y sistema de registro continuo. Incluye software MetaBridge para análisis avanzado.',
        'Farinógrafo Electrónico',
        'Bühler Group',
        '2025-04-04',
        'GAR-FAR-003',
        '2035-04-04',
        'Área de Aseguramiento de Calidad',
        'Activo'
    );
-- CLIENTES
INSERT INTO Clientes (
        req_certificado,
        nombre,
        rfc,
        nombre_contacto,
        puesto_contacto,
        correo_contacto,
        telefono_contacto,
        direccion_fiscal,
        estado,
        parametros,
        tipo_equipo
    )
VALUES (
        TRUE,
        'Molinos ABC',
        'ABC123456XYZ',
        'María López',
        'Compras',
        'maria.lopez@molinosabc.com',
        '55-1234-5678',
        'Calle 1, Colonia Centro, CDMX',
        'Activo',
        'Internacionales',
        'Alveógrafo'
    ),
    (
        TRUE,
        'Panificadora La Espiga',
        'ESP987654ZYX',
        'Roberto Díaz',
        'Calidad',
        'roberto.diaz@espiga.com',
        '55-9874-5432',
        'Avenida 2, Colonia Norte, CDMX',
        'Activo',
        'Personalizados',
        'Farinógrafo'
    ),
    (
        TRUE,
        'Panadería Artesanal Dorada',
        'PAD45678RTY',
        'Fernando Castillo',
        'Director de Producción',
        'fernando.castillo@dorada.com',
        '55-3456-7890',
        'Boulevard Industrial 234, Zona Este, Monterrey',
        'Activo',
        'Personalizados',
        'Alveógrafo'
    );
-- DIRECCIONES para cada cliente
INSERT INTO Direcciones (
        id_cliente,
        calle,
        num_Exterior,
        num_Interior,
        colonia,
        delegacion_alcaldia,
        codigo_postal,
        estado,
        notas
    )
VALUES (
        1,
        'Calle 1',
        '101',
        'A',
        'Centro',
        'Cuauhtémoc',
        '06000',
        'Ciudad de México',
        'Oficina principal'
    ),
    (
        2,
        'Avenida 2',
        '202',
        NULL,
        'Norte',
        'Gustavo A. Madero',
        '07000',
        'Ciudad de México',
        'Sucursal norte'
    ),
    (
        3,
        'Boulevard Industrial',
        '234',
        NULL,
        'Zona Este',
        'Monterrey',
        '64000',
        'Nuevo León',
        'Planta principal'
    );
-- PARÁMETROS para cada cliente
-- Cliente 2: Panificadora La Espiga
INSERT INTO Parametros (
        id_cliente,
        nombre_parametro,
        lim_Superior,
        lim_Inferior
    )
VALUES (2, 'Humedad', 13.5, 11.5),
    (2, 'Cenizas', 0.70, 0.50),
    (2, 'Gluten_Humedo', 30.0, 26.0),
    (2, 'Gluten_Seco', 10.0, 8.0),
    (2, 'Indice_Gluten', 90.0, 80.0),
    (2, 'Indice_Caida', 340.0, 290.0),
    (2, 'Almidon_Danado', 325.0, 300.0),
    (2, 'Farinograma_Absorcion_Agua', 61.0, 57.0),
    (2, 'Farinograma_Tiempo_Desarrollo', 3.0, 2.0),
    (2, 'Farinograma_Estabilidad', 9.5, 7.5),
    (2, 'Farinograma_Grado_Decaimiento', 75.0, 55.0);
-- Cliente 3: Panadería Artesanal Dorada
INSERT INTO Parametros (
        id_cliente,
        nombre_parametro,
        lim_Superior,
        lim_Inferior
    )
VALUES (3, 'Humedad', 13.8, 11.8),
    (3, 'Cenizas', 0.68, 0.52),
    (3, 'Gluten_Humedo', 31.0, 27.0),
    (3, 'Gluten_Seco', 10.5, 8.5),
    (3, 'Indice_Gluten', 92.0, 82.0),
    (3, 'Indice_Caida', 345.0, 295.0),
    (3, 'Almidon_Danado', 355.0, 300.0),
    (3, 'Alveograma_P', 98.0, 78.0),
    (3, 'Alveograma_L', 118.0, 95.0),
    (3, 'Alveograma_PL', 0.65, 0.45),
    (3, 'Alveograma_W', 290.0, 240.0),
    (3, 'Alveograma_IE', 1.15, 0.75);
-- PARÁMETROS para cada equipo
-- Equipo 1: Alveógrafo Internacional
INSERT INTO Parametros (
        id_equipo,
        nombre_parametro,
        lim_Superior,
        lim_Inferior
    )
VALUES (1, 'Humedad', 14.0, 12.0),
    (1, 'Cenizas', 0.65, 0.55),
    (1, 'Gluten_Humedo', 32.0, 28.0),
    (1, 'Gluten_Seco', 11.0, 9.0),
    (1, 'Indice_Gluten', 95.0, 85.0),
    (1, 'Indice_Caida', 350.0, 300.0),
    (1, 'Almidon_Danado', 375.0, 300.0),
    (1, 'Alveograma_P', 110.0, 90.0),
    (1, 'Alveograma_L', 130.0, 110.0),
    (1, 'Alveograma_PL', 0.7, 0.5),
    (1, 'Alveograma_W', 320.0, 270.0),
    (1, 'Alveograma_IE', 1.3, 0.9);
-- Equipo 2: Farinógrafo Internacional
INSERT INTO Parametros (
        id_equipo,
        nombre_parametro,
        lim_Superior,
        lim_Inferior
    )
VALUES (2, 'Humedad', 14.0, 12.0),
    (2, 'Cenizas', 0.65, 0.55),
    (2, 'Gluten_Humedo', 32.0, 28.0),
    (2, 'Gluten_Seco', 11.0, 9.0),
    (2, 'Indice_Gluten', 95.0, 85.0),
    (2, 'Indice_Caida', 350.0, 300.0),
    (2, 'Almidon_Danado', 30.0, 25.0),
    (2, 'Farinograma_Absorcion_Agua', 63.0, 59.0),
    (2, 'Farinograma_Tiempo_Desarrollo', 2.8, 1.8),
    (2, 'Farinograma_Estabilidad', 10.5, 8.5),
    (2, 'Farinograma_Grado_Decaimiento', 85.0, 65.0);
INSERT INTO Parametros (
        id_equipo,
        nombre_parametro,
        lim_Superior,
        lim_Inferior
    )
VALUES (3, 'Humedad', 14.2, 12.2),
    (3, 'Cenizas', 0.86, 0.56),
    (3, 'Gluten_Humedo', 35.0, 27.0),
    (3, 'Gluten_Seco', 11.8, 8.8),
    (3, 'Indice_Gluten', 103.0, 83.0),
    (3, 'Indice_Caida', 345.0, 295.0),
    (3, 'Almidon_Danado', 350.0, 300.0),
    (3, 'Alveograma_P', 105.0, 85.0),
    (3, 'Alveograma_L', 125.0, 105.0),
    (3, 'Alveograma_PL', 0.65, 0.45),
    (3, 'Alveograma_W', 310.0, 260.0),
    (3, 'Alveograma_IE', 1.25, 0.85);
-- Equipo 4: 
INSERT INTO Parametros (
        id_equipo,
        nombre_parametro,
        lim_Superior,
        lim_Inferior
    )
VALUES (4, 'Humedad', 14.2, 12.2),
    (4, 'Cenizas', 0.86, 0.56),
    (4, 'Gluten_Humedo', 35.0, 27.0),
    (4, 'Gluten_Seco', 11.8, 8.8),
    (4, 'Indice_Gluten', 103.0, 83.0),
    (4, 'Indice_Caida', 345.0, 295.0),
    (4, 'Almidon_Danado', 350.0, 300.0),
    (4, 'Alveograma_P', 105.0, 85.0),
    (4, 'Alveograma_L', 125.0, 105.0),
    (4, 'Alveograma_PL', 0.65, 0.45),
    (4, 'Alveograma_W', 310.0, 260.0),
    (4, 'Alveograma_IE', 1.25, 0.85);
-- Equipo 5: Farinógrafo 
INSERT INTO Parametros (
        id_equipo,
        nombre_parametro,
        lim_Superior,
        lim_Inferior
    )
VALUES (5, 'Humedad', 14.0, 12.0),
    (5, 'Cenizas', 0.65, 0.55),
    (5, 'Gluten_Humedo', 32.0, 28.0),
    (5, 'Gluten_Seco', 11.0, 9.0),
    (5, 'Indice_Gluten', 95.0, 85.0),
    (5, 'Indice_Caida', 350.0, 300.0),
    (5, 'Almidon_Danado', 30.0, 25.0),
    (5, 'Farinograma_Absorcion_Agua', 63.0, 59.0),
    (5, 'Farinograma_Tiempo_Desarrollo', 2.8, 1.8),
    (5, 'Farinograma_Estabilidad', 10.5, 8.5),
    (5, 'Farinograma_Grado_Decaimiento', 85.0, 65.0);
-- Equipo 6: Farinógrafo 
INSERT INTO Parametros (
        id_equipo,
        nombre_parametro,
        lim_Superior,
        lim_Inferior
    )
VALUES (6, 'Humedad', 14.0, 12.0),
    (6, 'Cenizas', 0.65, 0.55),
    (6, 'Gluten_Humedo', 32.0, 28.0),
    (6, 'Gluten_Seco', 11.0, 9.0),
    (6, 'Indice_Gluten', 95.0, 85.0),
    (6, 'Indice_Caida', 350.0, 300.0),
    (6, 'Almidon_Danado', 30.0, 25.0),
    (6, 'Farinograma_Absorcion_Agua', 63.0, 59.0),
    (6, 'Farinograma_Tiempo_Desarrollo', 2.8, 1.8),
    (6, 'Farinograma_Estabilidad', 10.5, 8.5),
    (6, 'Farinograma_Grado_Decaimiento', 85.0, 65.0);
-- INSPECCIONES COMPLETAS
-- Primero insertar las inspecciones
INSERT INTO Inspeccion (
        id_cliente,
        lote,
        secuencia,
        clave,
        fecha_inspeccion
    )
VALUES -- Cliente 1: Molinos ABC - Lote 001
    (
        1,
        'LOTE001',
        'A',
        'INSP-001',
        '2025-04-05 09:15:00'
    ),
    -- Cliente 2: Panificadora La Espiga - Lote 002
    (
        2,
        'LOTE002',
        'A',
        'INSP-002',
        '2025-04-12 10:45:00'
    ),
    -- Cliente 3: Panadería Artesanal Dorada - Lote 003
    (
        3,
        'LOTE003',
        'A',
        'INSP-003',
        '2025-04-18 08:30:00'
    ),
    (
        3,
        'LOTE003',
        'B',
        'INSP-004',
        '2025-04-19 11:00:00'
    );
-- Equipo ALV-001 (LOTE004-E para Cliente 1 - Alveógrafo)
INSERT INTO Inspeccion (
        id_equipo,
        lote,
        secuencia,
        clave,
        fecha_inspeccion
    )
VALUES(
        4,
        'LOTE004',
        'C',
        'INSP-005',
        '2025-04-20 12:00:00'
    );
INSERT INTO Resultado_Inspeccion (
        id_inspeccion,
        nombre_parametro,
        valor_obtenido,
        aprobado
    )
VALUES (1, 'Humedad', 13.5, TRUE),
    -- Dentro del rango del cliente [12.0-14.0]
    (1, 'Cenizas', 0.61, TRUE),
    -- Dentro del rango del cliente [0.55-0.65]
    (1, 'Gluten_Humedo', 30.0, TRUE),
    -- Dentro del rango del cliente [28.0-32.0]
    (1, 'Gluten_Seco', 10.0, TRUE),
    -- Dentro del rango del cliente [9.0-11.0]
    (1, 'Indice_Gluten', 90.0, TRUE),
    -- Dentro del rango del cliente [85.0-95.0]
    (1, 'Indice_Caida', 320.0, TRUE),
    -- Dentro del rango del cliente [300.0-350.0]
    (1, 'Almidon_Danado', 330.0, TRUE),
    -- Dentro del rango del cliente [300.0-375.0]
    (1, 'Alveograma_P', 100.0, TRUE),
    -- Dentro del rango del cliente [90.0-110.0]
    (1, 'Alveograma_L', 120.0, TRUE),
    -- Dentro del rango del cliente [110.0-130.0]
    (1, 'Alveograma_PL', 0.6, TRUE),
    -- Dentro del rango del cliente [0.5-0.7]
    (1, 'Alveograma_W', 295.0, TRUE),
    -- Dentro del rango del cliente [270.0-320.0]
    (1, 'Alveograma_IE', 1.1, TRUE);
-- Dentro del rango del cliente [0.9-1.3]
-- Inspección 2 (LOTE002-A para Cliente 2 - Farinógrafo)
INSERT INTO Resultado_Inspeccion (
        id_inspeccion,
        nombre_parametro,
        valor_obtenido,
        aprobado
    )
VALUES (2, 'Humedad', 12.7, TRUE),
    -- Dentro del rango del cliente [11.5-13.5]
    (2, 'Cenizas', 0.65, TRUE),
    -- Dentro del rango del cliente [0.5-0.7]
    (2, 'Gluten_Humedo', 28.0, TRUE),
    -- Dentro del rango del cliente [26.0-30.0]
    (2, 'Gluten_Seco', 9.0, TRUE),
    -- Dentro del rango del cliente [8.0-10.0]
    (2, 'Indice_Gluten', 85.0, TRUE),
    -- Dentro del rango del cliente [80.0-90.0]
    (2, 'Indice_Caida', 310.0, TRUE),
    -- Dentro del rango del cliente [290.0-340.0]
    (2, 'Almidon_Danado', 315.0, TRUE),
    -- Dentro del rango del cliente [300.0-325.0]
    (2, 'Farinograma_Absorcion_Agua', 59.0, TRUE),
    -- Dentro del rango del cliente [57.0-61.0]
    (2, 'Farinograma_Tiempo_Desarrollo', 2.5, TRUE),
    -- Dentro del rango del cliente [2.0-3.0]
    (2, 'Farinograma_Estabilidad', 8.5, TRUE),
    -- Dentro del rango del cliente [7.5-9.5]
    (2, 'Farinograma_Grado_Decaimiento', 65.0, TRUE);
-- Dentro del rango del cliente [55.0-75.0]
-- Inspección 3 (LOTE003-A para Cliente 3 - Alveógrafo)
INSERT INTO Resultado_Inspeccion (
        id_inspeccion,
        nombre_parametro,
        valor_obtenido,
        aprobado
    )
VALUES (3, 'Humedad', 13.0, TRUE),
    -- Dentro del rango del cliente [11.8-13.8]
    (3, 'Cenizas', 0.60, TRUE),
    -- Dentro del rango del cliente [0.52-0.68]
    (3, 'Gluten_Humedo', 29.0, TRUE),
    -- Dentro del rango del cliente [27.0-31.0]
    (3, 'Gluten_Seco', 9.5, TRUE),
    -- Dentro del rango del cliente [8.5-10.5]
    (3, 'Indice_Gluten', 87.0, TRUE),
    -- Dentro del rango del cliente [82.0-92.0]
    (3, 'Indice_Caida', 320.0, TRUE),
    -- Dentro del rango del cliente [295.0-345.0]
    (3, 'Almidon_Danado', 330.0, TRUE),
    -- Dentro del rango del cliente [300.0-355.0]
    (3, 'Alveograma_P', 88.0, TRUE),
    -- Dentro del rango del cliente [78.0-98.0]
    (3, 'Alveograma_L', 105.0, TRUE),
    -- Dentro del rango del cliente [95.0-118.0]
    (3, 'Alveograma_PL', 0.55, TRUE),
    -- Dentro del rango del cliente [0.45-0.65]
    (3, 'Alveograma_W', 265.0, TRUE),
    -- Dentro del rango del cliente [240.0-290.0]
    (3, 'Alveograma_IE', 0.95, TRUE);
-- Dentro del rango del cliente [0.75-1.15]
-- Inspección 4 (LOTE003-B para Cliente 3 - Alveógrafo) - con varios valores fuera de rango
INSERT INTO Resultado_Inspeccion (
        id_inspeccion,
        nombre_parametro,
        valor_obtenido,
        aprobado
    )
VALUES (4, 'Humedad', 11.7, FALSE),
    -- Fuera del rango del cliente [11.8-13.8]
    (4, 'Cenizas', 0.65, TRUE),
    -- Dentro del rango del cliente [0.52-0.68]
    (4, 'Gluten_Humedo', 26.5, FALSE),
    -- Fuera del rango del cliente [27.0-31.0]
    (4, 'Gluten_Seco', 9.0, TRUE),
    -- Dentro del rango del cliente [8.5-10.5]
    (4, 'Indice_Gluten', 84.0, TRUE),
    -- Dentro del rango del cliente [82.0-92.0]
    (4, 'Indice_Caida', 330.0, TRUE),
    -- Dentro del rango del cliente [295.0-345.0]
    (4, 'Almidon_Danado', 320.0, TRUE),
    -- Dentro del rango del cliente [300.0-355.0]
    (4, 'Alveograma_P', 88.0, TRUE),
    -- Dentro del rango del cliente [78.0-98.0]
    (4, 'Alveograma_L', 98.0, TRUE),
    -- Dentro del rango del cliente [95.0-118.0]
    (4, 'Alveograma_PL', 0.60, TRUE),
    -- Dentro del rango del cliente [0.45-0.65]
    (4, 'Alveograma_W', 230.0, FALSE),
    -- Fuera del rango del cliente [240.0-290.0]
    (4, 'Alveograma_IE', 0.90, TRUE);
-- Dentro del rango del cliente [0.75-1.15]
INSERT INTO Resultado_Inspeccion (
        id_inspeccion,
        nombre_parametro,
        valor_obtenido,
        aprobado
    )
VALUES -- RESULTADO_INSPECCION PARA EL EQUIPO 3 ALV-001
    (5, 'Humedad', 11.7, FALSE),
    (5, 'Cenizas', 0.70, TRUE),
    (5, 'Gluten_Humedo', 26.5, FALSE),
    (5, 'Gluten_Seco', 10, TRUE),
    (5, 'Indice_Gluten', 84.0, TRUE),
    (5, 'Indice_Caida', 330.0, TRUE),
    (5, 'Almidon_Danado', 320.0, TRUE),
    (5, 'Alveograma_P', 90.0, TRUE),
    (5, 'Alveograma_L', 98.0, TRUE),
    (5, 'Alveograma_PL', 0.60, TRUE),
    (5, 'Alveograma_W', 265.0, TRUE),
    (5, 'Alveograma_IE', 0.90, TRUE);