class Invoice {
  final int id;

  Invoice({this.id});

  factory Invoice.fromJson(Map<String, dynamic> data) {
    return Invoice(
      id: data['id'],
    );
  }
}