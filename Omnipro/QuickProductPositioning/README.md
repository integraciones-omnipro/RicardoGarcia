# Importador y exportador de posiciones

## Descripción:
La finalidad de este módulo es filtrar productos relacionados a una categoría específica y a su vez permite exportar una
lista de los productos relacionados a una categoría a través de un archivo CSV y posteriormente importar este mismo
archivo y actualizar los productos con las posiciones deseadas en categorías específicas.


## Exportador

1.- Una vez instalado el módulo, ir al menú principal y buscar la opción "Catálogo - Categorías"
2.- Elegir alguna categoría
3.- En la sección de "Products category" podrán encontrar un botón para poder exportar los productos relacionados a la
categoría en la que se encuentran.
4.- Al exportar este archivo, tendrán una referencia de los productos asignados a esa categoría.

## Importador:
* Para la funcionalidad de importador se agregó una nueva entidad en la sección de import, para poder importar las
  posiciones siga los siguientes pasos:

1.- Ir al menú principal de magento y buscar: System -> Data transfer -> Import
2.- En Entity Type se debe elegir la opción "Product Category Positions" y el proceso es el mismo que el importado de
productos por csv.