import 'dart:convert';

class Ingredient {
  final int id;
  final String name;

  final bool isVege;
  final bool isVegan;
  final bool isSweet;
  final bool isSalty;
  final bool isLiquid;
  final bool isRecipe;

  final String type;
  final int typeIndex;
  final String typeName;

  Ingredient({
    this.id,
    this.name,
    this.isVege,
    this.isVegan,
    this.isSweet,
    this.isSalty,
    this.isLiquid,
    this.isRecipe,
    this.type,
    this.typeIndex,
    this.typeName
  });

  factory Ingredient.fromJson(Map<String, dynamic> json) {
    return Ingredient(
      id: json['id'],
      name: json['name'],
      isVege: json['isVege'],
      isVegan: json['isVegan'],
      isSweet: json['isSweet'],
      isSalty: json['isSalty'],
      isLiquid: json['isLiquid'],
      isRecipe: json['isRecipe'],
      type: json['type'],
      typeIndex: json['typeIndex'],
      typeName: json['typeName'],
    );
  }
}