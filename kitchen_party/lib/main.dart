import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:kitchen_party/Ingredient.dart';
import 'package:kitchen_party/RecipeIngredient.dart';
import 'dart:developer';

import 'Recipe.dart';

void main() {
  runApp(MyApp());
}

class MyApp extends StatelessWidget {
  // This widget is the root of your application.
  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Kitchen Party',
      theme: ThemeData(
        // This is the theme of your application.
        //
        // Try running your application with "flutter run". You'll see the
        // application has a blue toolbar. Then, without quitting the app, try
        // changing the primarySwatch below to Colors.green and then invoke
        // "hot reload" (press "r" in the console where you ran "flutter run",
        // or simply save your changes to "hot reload" in a Flutter IDE).
        // Notice that the counter didn't reset back to zero; the application
        // is not restarted.
        primarySwatch: Colors.blue,
      ),
      darkTheme: ThemeData.dark(),
      themeMode: ThemeMode.dark,
      debugShowCheckedModeBanner: false,
      home: MyHomePage(title: 'Kitchen Party'),
    );
  }
}

class MyHomePage extends StatefulWidget {
  MyHomePage({Key key, this.title}) : super(key: key);

  // This widget is the home page of your application. It is stateful, meaning
  // that it has a State object (defined below) that contains fields that affect
  // how it looks.

  // This class is the configuration for the state. It holds the values (in this
  // case the title) provided by the parent (in this case the App widget) and
  // used by the build method of the State. Fields in a Widget subclass are
  // always marked "final".

  final String title;

  @override
  _MyHomePageState createState() => _MyHomePageState();
}

class _MyHomePageState extends State<MyHomePage> {
  List<Recipe> recipes = [];
  List<Ingredient> ingredients = [];
  List<RecipeIngredient> kitchen = [];

  @override
  void initState() {
    super.initState();
    this.fetchData();
  }

  Future<void> fetchData() async {
    final response = await http.get(Uri.https('www.jeremy-achain.dev', 'kitchen/recipes'));

    if (response.statusCode == 200) {
      final List<dynamic> recipes = jsonDecode(response.body)['recipes'];
      final List<dynamic> ingredients = jsonDecode(response.body)['ingredients'];
      final List<dynamic> kitchen = jsonDecode(response.body)['kitchen'];

      recipes.forEach((element) {
        this.recipes.add(Recipe.fromJson(element));
      });
      ingredients.forEach((element) {
        this.ingredients.add(Ingredient.fromJson(element));
      });
      kitchen.forEach((element) {
        this.kitchen.add(RecipeIngredient.fromJson(element));
      });
      setState(() {

      });
    } else {
      throw Exception('Failed to load recipes');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.title),
      ),
      body: GridView.builder(
          itemCount: this.recipes.length,
          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: 2,
            childAspectRatio: 420/280,
          ),
          itemBuilder: (context, index) {
            return Container(
                padding: EdgeInsets.fromLTRB(10, 10, 10, 0),
                child: recipeCard(this.recipes[index])
            );
          }
      )
      /*body: ListView.builder(
        itemCount: this.recipes.length,
        itemBuilder: (context, index) {
          return Container(
              padding: EdgeInsets.fromLTRB(10, 10, 10, 0),
              height: 220,
              child: recipeCard(this.recipes[index])
          );
        }
      )*/
    );
  }



  Widget recipeCard(Recipe recipe) {
    return Card(
      elevation: 5,
      child: Container(
        decoration: new BoxDecoration(
            image: DecorationImage(
                image: NetworkImage('https://www.jeremy-achain.dev/kitchen/' + recipe.slug + '/image'),
                fit: BoxFit.fitWidth
            )
        ),
        child: Column(
          children: [
            Container(
                padding: EdgeInsets.all(10),
                height: 100,
                width: double.maxFinite,
                color: Colors.black45,
                child: Text(recipe.name)
            )
          ],
        ),
      )
    );
  }
}
