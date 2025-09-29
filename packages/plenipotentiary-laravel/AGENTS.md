---

## 🎯 Your Role as Agent

### **Primary Responsibilities**

1. **Architecture Guidance**: Help design clean, maintainable patterns that work across diverse APIs
2. **Contract Design**: Ensure contracts are provider-agnostic and flexible enough for real-world use
3. **Implementation Quality**: Help make concrete implementations exemplary templates for scaffolding
4. **Testing Strategy**: Ensure comprehensive test coverage that validates both contracts and implementations
5. **Documentation**: Help create clear, actionable documentation for the scaffolding system

### **Key Principles to Maintain**

- **Provider Semantics**: Preserve each API's natural operation names and patterns
- **No Over-Abstraction**: Don't force APIs into patterns they don't naturally fit
- **Contract-Driven**: All implementations must satisfy their contracts
- **Test-First**: Every feature needs comprehensive test coverage
- **Scaffolding-Ready**: Code must be template-quality for Artisan generation

### **Current Focus Areas**

1. **Contract Completeness**: Ensure all necessary contracts exist and are well-designed
2. **Implementation Quality**: Make Google Ads, eBay, and OpenAI implementations exemplary
3. **Test Coverage**: Comprehensive testing of both contracts and concrete implementations
4. **Scaffolding Preparation**: Structure code to be easily templated for Artisan commands
5. **Documentation**: Clear examples and patterns for future scaffolding

---

## 🚀 Example Usage Patterns

### **CRUD Operations**
```php
$result = $campaignGateway->create($campaignDto);
if ($result->isOk()) {
    $campaign = $result->unwrap();
}
```

### **Flexible Endpoint Operations**
```php
$result = $openaiGateway->call('createCompletion', [
    'model' => 'gpt-3.5-turbo',
    'messages' => [['role' => 'user', 'content' => 'Hello!']]
]);
```

### **Consistent Error Handling**
```php
if ($result->isInvalid()) {
    $violations = $result->violations();
} elseif ($result->isErr()) {
    $error = $result->error();
}
```

---

## 📋 Development Guidelines

- **Contracts First**: Always define contracts before implementations
- **Provider-Agnostic**: Keep contracts free of provider-specific details
- **Comprehensive Testing**: Every contract and implementation needs full test coverage
- **Template Quality**: Code should be exemplary enough to serve as scaffolding templates
- **Documentation**: Every pattern should be clearly documented with examples

---

✅ **Context Complete**: You now understand the current state, goals, and architecture of Plenipotentiary-Laravel. Focus on helping create exemplary implementations that will become the foundation for our scaffolding system.

ℹ️ Need sandbox workflow or contributor process details? See the root guide in `/AGENTS.md`.
