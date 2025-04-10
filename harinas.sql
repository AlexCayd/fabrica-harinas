CREATE DATABASE harinas;
USE harinas;
-- Tabla Usuarios
CREATE TABLE Usuarios (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(100) UNIQUE NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    rol ENUM('TI',
            'Gerencia de Control de Calidad', 
            'Laboratorio', 
            'Gerencia de Aseguramiento de Calidad', 
            'Gerente de Planta', 
            'Director de Operaciones') NOT NULL
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
    estado ENUM('Activo','Inactivo', 'Baja') NOT NULL DEFAULT 'Activo',
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
    id_equipo INT NOT NULL,
    id_cliente INT,
    nombre_parametro VARCHAR(100) NOT NULL,
    tipo ENUM('Personalizado', 'Internacional') NOT NULL,
    lim_Superior INT NOT NULL,
    lim_Inferior INT NOT NULL,
    FOREIGN KEY (id_equipo) REFERENCES Equipos_Laboratorio(id_equipo),
    FOREIGN KEY (id_cliente) REFERENCES Clientes(id_cliente)
);

-- Tabla Inspeccion
CREATE TABLE Inspeccion (
    id_inspeccion INT PRIMARY KEY AUTO_INCREMENT,
    id_cliente INT NOT NULL,
    lote VARCHAR(10),
    secuencia CHAR(3),
    clave VARCHAR(13) NOT NULL,
    fecha_inspeccion TIMESTAMP,
    humedad DECIMAL(5,2),
    cenizas DECIMAL(5,2),
    gluten_humedo DECIMAL(5,2),
    gluten_sec DECIMAL(5,2),
    indice_gluten DECIMAL(5,2),
    indice_caida INT,
    almidon_danado DECIMAL(5,2),
    color VARCHAR(50),
    granulometria VARCHAR(100),
    microbiologicos VARCHAR(255),
    alveograma_p DECIMAL(5,2),
    alveograma_l DECIMAL(5,2),
    alveograma_pl DECIMAL(5,2),
    alveograma_w DECIMAL(5,2),
    alveograma_ie DECIMAL(5,2),
    FOREIGN KEY (id_cliente) REFERENCES Clientes(id_cliente)
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
    fecha_envio DATE,
    fecha_caducidad DATE NOT NULL,
    desviacion DECIMAL(10,2),
    FOREIGN KEY (id_inspeccion) REFERENCES Inspeccion(id_inspeccion)
);

-- Tabla Hist_Certificados
CREATE TABLE Hist_Certificados (
    id INT PRIMARY KEY AUTO_INCREMENT,
    fecha_subida TIMESTAMP,
    id_certificado INT NOT NULL,
    FOREIGN KEY (id_certificado) REFERENCES Certificados(id_certificado)
);