import { BrowserRouter, Routes, Route } from "react-router-dom";
import NavBar from "./components/NavBar";
import LoginPage from "./pages/LoginPage";
import HomePage from "./pages/HomePage";
import CartPage from "./pages/CartPage";
import PrivateRoute from "./components/PrivateRoute";
import RegisterPage from "./pages/RegisterPage";
import IngredientsPage from "./pages/IngredientsPage";
import IngredientDetailsPage from "./pages/IngredientDetailsPage";
import RecipesPage from "./pages/RecipesPage";
import RecipeDetailsPage from "./pages/RecipeDetailsPage"; 
import CommunityRecipesPage from "./pages/CommunityRecipesPage";
import OrdersPage from "./pages/OrdersPage";
import AdminRoute from "./components/AdminRoute";
import AdminDashboardPage from "./pages/admin/AdminDashboardPage";
import AdminIngredientsPage from "./pages/admin/AdminIngredientsPage";
import AdminRecipesPage from "./pages/admin/AdminRecipesPage";
import AdminOrdersPage from "./pages/admin/AdminOrdersPage";
import { useState } from 'react'
import reactLogo from './assets/react.svg'
import viteLogo from '/vite.svg'

function App() {

  return (
    <BrowserRouter>
      <NavBar />
      <Routes>
        <Route path="/" element={<HomePage />} />
        <Route path="/login" element={<LoginPage />} />
        <Route path="/register" element={<RegisterPage />} />
        <Route path="/ingredients" element={<IngredientsPage />} />
        <Route path="/ingredients/:id/:slug" element={<IngredientDetailsPage />} />
        <Route path="/recipes" element={<RecipesPage />} />
        <Route path="/community-recipes" element={<CommunityRecipesPage />} />
        <Route path="/recipes/:id/:slug" element={<RecipeDetailsPage />} />
        <Route
          path="/recipes/:recipeId/:recipeSlug/ingredients/:id/:ingredientSlug"
          element={<IngredientDetailsPage />}
        />
        <Route element={<AdminRoute />}>
          <Route path="/admin" element={<AdminDashboardPage />} />
          <Route path="/admin/ingredients" element={<AdminIngredientsPage />} />
          <Route path="/admin/recipes" element={<AdminRecipesPage />} />
          <Route path="/admin/orders" element={<AdminOrdersPage />} />
        </Route>
        <Route element={<PrivateRoute />}>
          <Route path="/cart" element={<CartPage />} />
          <Route path="/orders" element={<OrdersPage />} />
        </Route>
      </Routes>
    </BrowserRouter>
  )
}

export default App
