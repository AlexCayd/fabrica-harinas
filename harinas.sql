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
-- EQUIPOS DE LABORATORIO (uno de cada tipo)
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
        3,
        'ALV-001',
        'Alveógrafo',
        'Chopin',
        'AlveoLab',
        'ALV12345',
        'Alveógrafo para Parámetros Internacionales',
        'Alveógrafo Internacional',
        'Proveedor A',
        '2025-04-15',
        'GAR-ALV-001',
        '2035-04-15',
        'Laboratorio Principal',
        'Activo'
    ),
    (
        3,
        'FAR-001',
        'Farinógrafo',
        'Brabender',
        'FarinoLab',
        'FAR54321',
        'Farinógrafo para Parámetros Internacionales',
        'Farinógrafo Internacional',
        'Proveedor B',
        '2025-04-15',
        'GAR-FAR-001',
        '2035-04-15',
        'Laboratorio Principal',
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
    );
-- PARÁMETROS para cada cliente
-- Cliente 1: Molinos ABC
INSERT INTO Parametros (
        id_cliente,
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
    (1, 'Alveograma_P', 100.0, 80.0),
    (1, 'Alveograma_L', 120.0, 100.0),
    (1, 'Alveograma_PL', 0.6, 0.4),
    (1, 'Alveograma_W', 300.0, 250.0),
    (1, 'Alveograma_IE', 1.2, 0.8);
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
    (2, 'Farinograma_Absorcion_Agua', 61.0, 57.0),
    (2, 'Farinograma_Tiempo_Desarrollo', 3.0, 2.0),
    (2, 'Farinograma_Estabilidad', 9.5, 7.5),
    (2, 'Farinograma_Grado_Decaimiento', 75.0, 55.0);
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