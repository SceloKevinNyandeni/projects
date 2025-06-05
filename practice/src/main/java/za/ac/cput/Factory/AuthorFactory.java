package za.ac.cput.Factory;

import za.ac.cput.Domain.Author;
import za.ac.cput.Domain.Book;

import java.util.List;

public class AuthorFactory {
    public static Author createAuthor(Long authorId, String authorName, List<Book> books) {
        return new Author.AuthorBuilder()
                .setAuthorId(authorId)
                .setAuthorName(authorName)
                .setBooks(books)
                .build();
    }
}
