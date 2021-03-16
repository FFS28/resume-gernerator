import 'dart:convert';

import 'package:kitchen_party/Ingredient.dart';

class RecipeIngredient {
  final int id;
  final String name;

  final double quantity;
  final double equivalentGram;
  final String unit;
  final String unitName;
  final String measure;
  final String measureStr;

  final bool isInKitchen;

  Ingredient ingredient;

  RecipeIngredient({
    this.id,
    this.name,
    this.quantity,
    this.equivalentGram,
    this.unit, this.unitName,
    this.measure, this.measureStr,
    this.isInKitchen,
    this.ingredient
  });

  factory RecipeIngredient.fromJson(Map<String, dynamic> json) {
    return RecipeIngredient(
      id: json['id'],
      name: json['name'],
      quantity: json['quantity'],
      equivalentGram: json['equivalentGram'],
      unit: json['unit'],
      unitName: json['unitName'],
      measure: json['measure'],
      measureStr: json['measureStr'],
      isInKitchen: json['kitchen'],
      ingredient: Ingredient.fromJson(json['ingredient']),
    );
  }
}