# Importador y exportador de posiciones

## Descripción:
La finalidad de este módulo es filtrar productos relacionados a una categoria especifica y asu vez permite exportar una lista de los productos filtrados para que el usuario pueda editar las posiciones dentro de una categoría a travéz de un archivo CSV y posteriormente importar este mismo archivo y actualizar los productos con las posiciones deseadas en categorías especificas.

## Configuración de administrador

1.- Ir a Stores -> Settings -> Configuration -> OmniPro tab -> Quick Product Positioning -> General Configurations -> enable
* Hbilitar el módulo *

## Configuración de Exportador

1.- Una vez habilitado el módulo, ir al menú principal y buscar la opción "Order Products"
2.- Elegir la opción "Product Position"
3.- En esta sección encontraremos una lista de productos con la siguiente infomación:

* ID -> Se refiere a la entidad que se ocupa en la base de datos en la tabla "catalog_category_product"
* Category ID -> Categoria  la que se encuentra relacionado (Un producto puede estar asignado a varias categorias)
* Product ID -> ID del producto
* Position -> Posición en la que se encuentra actualmente el producto
* SKU -> SKU del producto
* Name -> Nombre del producto
* Product Type

  En esta tabla podremos filtrar con los siguientes datos:

* ID
* Category ID 
* Product ID
* Position 

(La idea es poder filtar con todos los datos de la tabla, pero al módulo aun no esta terminado)

Una vez que se hayá aplicado el filtro, se pueden esportar los datos filtrados en la esquina superior derecha.

El archivo que descargara será algo similar a esto:


| ID | Category Id | Product Id | Position | SKU | Name | Product Type |  
----|-------------|------------|----------|--|---|-----------| 
| 1  | 24          | 1029       | 1        |  |   |           |


+ Actualmente el módulo no cuenta con el inner join para unir la tabla que contine la información de sku, Name y product type 
+ (Funcionalidad pendiente e iportante para que los datos del producto puedan ser claros para un administrador sin acceso a los ID's)

## Configuración de Importador:
* Para la funcionalidad de importador se agregó una nueva entidad en la sección de import, para poder importar las posiciones siga los siguientes pasos:
1.- Ir al menú principal de magento y buscar: System -> Data transfer -> Import
2.- En Entity Type se debe elegir la opción "Positions" y el proceso es el mismo que el importado de productos por csv.

El archivo que se permite importar por el momento es como el siguiente ejemplo:

| ID | Category Id | Product Id | Position |
----|-------------|------------|----------|
| 1  | 24          | 1029       | 1        | 
| 2  | 25          | 1029       | 2        | 

(El módulo no esta completo, pero la intención es agregar mas información relevante de los productos para una mejor busqueda)

Adicional de agregó un nuevo atributo para habilitar productos destacados (funcionalidad pendiente)